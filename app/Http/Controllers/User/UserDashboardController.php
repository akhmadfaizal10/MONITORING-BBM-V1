<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TableManager;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Pastikan pengguna memiliki perusahaan
        if (!$user || !$user->company) {
            return redirect()->route('home')->with('error', 'Unauthorized access');
        }

        // Dapatkan nama tabel perusahaan menggunakan TableManager
        $tableName = TableManager::getCompanyTable($user->company);

        // Pastikan tabel perusahaan ada, jika tidak buat
        TableManager::ensureCompanyTable($user->company);

        // Dapatkan data untuk dashboard
        $chartData = $this->prepareChartData($user);

        return view('dashboard-user', [
    'totalCompanies' => 1, // Karena pengguna hanya berhubungan dengan satu perusahaan
    'totalVehicles' => $chartData['totalVehicles'],
    'totalOutliers' => $chartData['totalOutliers'], // Mengambil total outliers
    'statusDistribution' => json_encode($chartData['statusDistribution']),
    'fuelConsumptionData' => json_encode($chartData['fuelConsumptionData']),
    'statusSummary' => $chartData['statusSummary'],
    'company' => $user->company,
]);
    }

    public function getData(Request $request)
{
    $user = Auth::user();
    $company = $request->query('company', $user->company);
    $status = $request->query('status', 'all');
    $page = (int) $request->query('page', 1);
    $perPage = 50;
    $date = $request->query('date', 'today');

    // Mengambil data dari tabel berdasarkan perusahaan
    $tableName = TableManager::getCompanyTable($company);
    if (!$tableName) {
        return response()->json([
            'data' => [],
            'total' => 0,
            'error' => 'Company table not found'
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
        ->select('vehicle_id', 'status', 'fuel_in', 'fuel_out', 'recorded_at') // Hanya memilih kolom yang dibutuhkan
        ->orderBy('recorded_at', 'desc')
        ->offset(($page - 1) * $perPage)
        ->limit($perPage)
        ->get();

    return response()->json([
        'data' => $data,
        'total' => $total,
        'perPage' => $perPage,
        'currentPage' => $page,
    ]);
}

  private function prepareChartData($user)
{
    $totalVehicles = 0;
    $statusCounts = [
        'normal' => 0,
        'refuel' => 0,
        'theft' => 0,
        'plugged_theft' => 0,
    ];
    $totalOutliers = 0; // Total outliers akan dihitung di sini
    $statusSummary = [];
    $fuelData = [];

    $companyName = $user->company;
    $tableName = TableManager::getCompanyTable($companyName);

    // Pastikan tabel ada dan tidak kosong
    TableManager::ensureCompanyTable($companyName);

    // Mendapatkan data kendaraan terbaru
    $latestRecords = DB::table($tableName)
        ->select('vehicle_id', 'status', 'nik', 'fuel_out', 'recorded_at')
        ->whereIn('id', function ($q) use ($tableName) {
            $q->select(DB::raw('MAX(id)'))->from($tableName)->groupBy('vehicle_id');
        })
        ->get();

    $totalVehicles = $latestRecords->count();

    foreach ($latestRecords as $record) {
        // Hitung status kendaraan
        if (isset($statusCounts[$record->status])) {
            $statusCounts[$record->status]++;
        }

        // Tambahkan status kendaraan ke summary
        $statusSummary[] = [
            'nik' => $record->nik,
            'vehicle_id' => $record->vehicle_id,
            'status' => $record->status,
            'fuel_out' => $record->fuel_out,
            'recorded_at' => $record->recorded_at,
        ];

        // Identifikasi outlier berdasarkan status
        if ($record->status === 'theft' || $record->status === 'plugged_theft') {
            $totalOutliers++; // Hitung total outlier
        }
    }

    // Hitung konsumsi BBM untuk grafik
    $fuelHistory = DB::table($tableName)
        ->select(DB::raw('DATE(recorded_at) as date'), DB::raw('SUM(fuel_out) as total_consumption'))
        ->where('recorded_at', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    foreach ($fuelHistory as $h) {
        $fuelData[$h->date] = $h->total_consumption;
    }

    $statusDistribution = [
        'labels' => ['Normal', 'Refuel', 'Theft'],
        'data' => [
            $statusCounts['normal'],
            $statusCounts['refuel'],
            $statusCounts['theft'],
            
        ],
    ];

    return [
        'totalVehicles' => $totalVehicles,
        'totalOutliers' => $totalOutliers, // Mengembalikan total outliers
        'statusDistribution' => $statusDistribution,
        'fuelConsumptionData' => [
            'labels' => array_keys($fuelData),
            'datasets' => [[
                'label' => 'Konsumsi BBM',
                'data' => array_values($fuelData),
                'borderColor' => 'rgba(25,135,84,1)',
                'backgroundColor' => 'rgba(25,135,84,0.3)',
            ]]
        ],
        'statusSummary' => $statusSummary,
    ];
}
}