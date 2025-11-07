<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Hanya mengizinkan akses untuk admin
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Unauthorized role');
        }

        $chartData = $this->prepareChartData($user);

        return view('dashboard', [
            'totalCompanies'      => $chartData['totalCompanies'],
            'totalVehicles'       => $chartData['totalVehicles'],
            'statusDistribution'  => json_encode($chartData['statusDistribution']),
            'fuelConsumptionData' => json_encode($chartData['fuelConsumptionData']),
            'outlierData'         => json_encode($chartData['outlierData']),
            'statusSummary'       => $chartData['statusSummary'],
        ]);
    }

    public function getData(Request $request)
    {
        $user = Auth::user(); // Harus login sebagai admin

        // Periksa jika user bukan admin
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'data' => [], 'total' => 0, 'error' => 'Unauthorized role'
            ], 403);
        }

        $company = $request->query('company');
        $status  = $request->query('status', 'all');
        $page    = (int) $request->query('page', 1);
        $perPage = 50;
        $date    = $request->query('date', 'today');

        Log::info('Dashboard data request', [
            'user_id' => $user?->id,
            'role'    => $user?->role,
            'company' => $company,
        ]);

        $tableName = $this->resolveCompanyTable($company);
        if (!$tableName) {
            return response()->json([
                'data' => [], 'total' => 0, 'error' => 'Company table not found'
            ], 404);
        }

        $query = DB::table($tableName);
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($date === 'today') {
            $query->whereDate('recorded_at', Carbon::today());
        } elseif ($date === '7days') {
            $query->whereBetween('recorded_at', [
                Carbon::now()->subDays(7)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        } elseif (str_contains($date, '_')) {
            [$start, $end] = explode('_', $date);
            if ($start && $end) {
                $query->whereBetween('recorded_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
            }
        }

        $total = $query->count();
        $data = $query
            ->select('vehicle_id', 'status', 'fuel_in', 'fuel_out', 'recorded_at') // Hapus 'nik' dari sini
            ->orderBy('recorded_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
        ]);
    }

    private function resolveCompanyTable($company)
    {
        if (!$company) return null;
        $formatted = strtolower(trim(str_replace(' ', '_', $company)));
        $match = DB::select("SHOW TABLES LIKE 'company_{$formatted}%'");
        return !empty($match) ? array_values((array)$match[0])[0] : null;
    }

    private function prepareChartData($user)
    {
        $allTables = DB::select("SHOW TABLES LIKE 'company_%'");
        $statusCounts = ['normal' => 0, 'refuel' => 0, 'theft' => 0, 'plugged_theft' => 0];
        $fuelData = [];
        $outliers = [];
        $totalVehicles = 0;
        $companyNames = [];
        $companyAggregates = [];

        foreach ($allTables as $table) {
            $tableName = array_values((array) $table)[0];
            if (!DB::getSchemaBuilder()->hasTable($tableName)) continue;

            $companyName = ucwords(str_replace('_', ' ', preg_replace('/_k\d+|_v\d+$/', '', str_replace('company_', '', $tableName))));

            if (!in_array($companyName, $companyNames)) {
                $companyNames[] = $companyName;
            }

            $latestRecords = DB::table($tableName)
                ->select('vehicle_id', 'status', 'fuel_out', 'recorded_at') // Hapus 'nik' di sini juga
                ->whereIn('id', function ($q) use ($tableName) {
                    $q->select(DB::raw('MAX(id)'))->from($tableName)->groupBy('vehicle_id');
                })
                ->get();

            $totalVehicles += $latestRecords->count();

            foreach (['normal', 'refuel', 'theft', 'plugged_theft'] as $st) {
                $statusCounts[$st] += $latestRecords->where('status', $st)->count();
            }

            $companyStatus = [
                'normal' => DB::table($tableName)->where('status', 'normal')->count(),
                'refuel' => DB::table($tableName)->where('status', 'refuel')->count(),
                'theft' => DB::table($tableName)->where('status', 'theft')->count(),
                'plugged_theft' => DB::table($tableName)->where('status', 'plugged_theft')->count(),
            ];

            $companyAggregates[$companyName] = [
                'company' => $companyName,
                'normal' => $companyStatus['normal'],
                'refuel' => $companyStatus['refuel'],
                'theft' => $companyStatus['theft'],
                'plugged_theft' => $companyStatus['plugged_theft'],
            ];

            $fuelHistory = DB::table($tableName)
                ->select(DB::raw('DATE(recorded_at) as date'), DB::raw('SUM(fuel_out) as total_consumption'))
                ->where('recorded_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            foreach ($fuelHistory as $h) {
                $fuelData[$companyName][$h->date] = ($fuelData[$companyName][$h->date] ?? 0) + $h->total_consumption;
            }

            foreach ($latestRecords as $r) {
                if ($r->fuel_out > 50) {
                    $outliers[] = [
                        'company' => $companyName,
                        'vehicle_id' => $r->vehicle_id,
                        'value' => $r->fuel_out,
                        'time' => $r->recorded_at,
                    ];
                }
            }
        }

        $statusDistribution = [
            'labels' => ['Normal', 'Refuel', 'Theft', 'Plugged Theft'],
            'data' => array_values($statusCounts),
        ];

        $fuelConsumptionData = [
            'labels' => collect($fuelData)->flatMap(fn($d) => array_keys($d))->unique()->sort()->values(),
            'datasets' => [],
        ];

        foreach ($fuelData as $c => $d) {
            $dataset = [
                'label' => $c,
                'data' => [],
                'borderColor' => 'rgba(' . rand(0,255) . ',' . rand(0,255) . ',' . rand(0,255) . ', 1)',
                'fill' => false,
                'tension' => 0.1,
            ];
            foreach ($fuelConsumptionData['labels'] as $l) {
                $dataset['data'][] = $d[$l] ?? 0;
            }
            $fuelConsumptionData['datasets'][] = $dataset;
        }

        return [
            'totalCompanies' => count($companyNames),
            'totalVehicles' => $totalVehicles,
            'statusDistribution' => $statusDistribution,
            'fuelConsumptionData' => $fuelConsumptionData,
            'outlierData' => $outliers,
            'statusSummary' => array_values($companyAggregates),
        ];
    }
}