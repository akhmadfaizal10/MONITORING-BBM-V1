<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
{
    $user = Auth::user();

    // Guard admin
    if (!$user || $user->role !== 'admin') {
        return redirect()->route('home')->with('error', 'Unauthorized role');
    }

    // Ambil semua data dashboard
    $chartData = $this->prepareChartData();

    return view('dashboard', [
        // Kartu statistik
        'totalCompanies'    => $chartData['totalCompanies'],
        'totalVehicles'     => $chartData['totalVehicles'],
        'totalRefuelLatest' => $chartData['totalRefuelLatest'],

        // Pie chart carousel (per perusahaan)
        'statusDistribution'=> $chartData['statusDistribution'],

        // Line chart carousel (per perusahaan, per kendaraan)
        'lineCharts'        => $chartData['lineCharts'],

        // Outlier
        'outlierData'       => json_encode($chartData['outlierData']),
    ]);
}

 private function prepareChartData()
{
    $tables = DB::select("SHOW TABLES LIKE 'company_%'");

    $companies  = [];
    $lineCharts = [];
    $outliers   = [];

    $totalVehicles     = 0;
    $totalRefuelLatest = 0;

    foreach ($tables as $t) {
        $tableName = array_values((array) $t)[0];

        // Nama perusahaan
        $company = ucwords(str_replace('_', ' ',
            preg_replace('/_k\d+|_v\d+$/', '', str_replace('company_', '', $tableName))
        ));

        if (!isset($companies[$company])) {
            $companies[$company] = [
                'normal' => 0,
                'refuel' => 0,
                'theft' => 0,
                'plugged_theft' => 0,
                'vehicles' => []
            ];
        }

        /* ===============================
         * PIE CHART → STATUS TERAKHIR
         * =============================== */
        $latest = DB::table($tableName)
            ->whereIn('id', function ($q) use ($tableName) {
                $q->select(DB::raw('MAX(id)'))
                  ->from($tableName)
                  ->groupBy('vehicle_id');
            })
            ->select('vehicle_id', 'status', 'fuel_out', 'recorded_at')
            ->get();

        foreach ($latest as $row) {

            if (isset($companies[$company]['vehicles'][$row->vehicle_id])) {
                continue;
            }

            $companies[$company]['vehicles'][$row->vehicle_id] = true;
            $companies[$company][$row->status]++;
            $totalVehicles++;

            if ($row->status === 'refuel') {
                $totalRefuelLatest++;
            }

            if ($row->fuel_out > 50) {
                $outliers[] = [
                    'company' => $company,
                    'vehicle_id' => $row->vehicle_id,
                    'value' => $row->fuel_out,
                    'time' => $row->recorded_at,
                ];
            }
        }

        /* ===============================
         * LINE CHART → PER VEHICLE (7 HARI)
         * =============================== */
        $history = DB::table($tableName)
            ->select(
                'vehicle_id',
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('SUM(fuel_out) as total')
            )
            ->where('recorded_at', '>=', now()->subDays(7))
            ->groupBy('vehicle_id', 'date')
            ->orderBy('date')
            ->get();

        $dates = $history->pluck('date')->unique()->sort()->values()->toArray();

        $datasets = [];
        foreach ($history->groupBy('vehicle_id') as $vehicle => $rows) {
            $map = $rows->pluck('total', 'date')->toArray();

            $datasets[] = [
                'label' => $vehicle,
                'data'  => array_map(fn($d) => $map[$d] ?? 0, $dates),
                'borderColor' => 'rgba('.rand(50,200).','.rand(50,200).','.rand(50,200).',1)',
                'tension' => 0.3,
                'fill' => false,
            ];
        }

        $lineCharts[] = [
            'company' => $company,
            'labels' => $dates,
            'datasets' => $datasets,
        ];
    }

    /* ===============================
     * PIE FINAL
     * =============================== */
    $companyPieCharts = [];
    foreach ($companies as $company => $s) {
        $companyPieCharts[] = [
            'company' => $company,
            'labels' => ['Normal', 'Refuel', 'Theft'],
            'data' => [
                $s['normal'],
                $s['refuel'],
                $s['theft'],
               
            ]
        ];
    }

    return [
        'totalCompanies'     => count($companies),
        'totalVehicles'      => $totalVehicles,
        'totalRefuelLatest'  => $totalRefuelLatest,
        'statusDistribution' => $companyPieCharts,
        'lineCharts'         => $lineCharts, // ✅ INI YANG HILANG
        'outlierData'        => $outliers,
    ];
}


}
