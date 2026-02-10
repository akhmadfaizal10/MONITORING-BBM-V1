<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TableManager;
use Carbon\Carbon;
use App\Models\BudgetPerusahaan;
use App\Models\NikFuelPrice;


class AnalysisController extends Controller
{
   public function index(Request $request)
{
    $user = Auth::user();
    $company = $user->company;

    // 🔹 Ambil parameter filter
    $start = $request->input('start', Carbon::now()->subDays(7)->startOfDay());
    $end   = $request->input('end', Carbon::now()->endOfDay());
    $nik   = $request->input('nik');

    // 🔹 Ambil nama tabel sesuai company/nik
    $table = $nik
        ? TableManager::getNikTable($company, $nik)
        : TableManager::getCompanyTable($company);

    // 🔹 Cek tabel
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        return view('analysis.index', [
            'message' => "Tabel $table belum memiliki data.",
            'company' => $company,
            'start' => $start,
            'end' => $end,
            'nik' => $nik
        ]);
    }

    // 🔹 Ambil data
    $records = DB::table($table)
        ->whereBetween('recorded_at', [$start, $end])
        ->orderBy('recorded_at', 'asc')
        ->get();

    if ($records->isEmpty()) {
        return view('analysis.index', [
            'message' => "Tidak ada data dari $start sampai $end",
            'company' => $company,
            'start' => $start,
            'end' => $end,
            'nik' => $nik
        ]);
    }

    // ==============================
    // 📊 HITUNG DATA
    // ==============================
    $fuelUsedPerHour  = [];
    $fuelUsedPerDay   = [];
    $fuelUsedPerMonth = [];
    $fuelUsedAll      = [];

    // ✅ Ambil bulan & tahun dari filter
    $monthKey = Carbon::parse($start)->format('Y-m');
    $yearKey  = Carbon::parse($start)->format('Y');

    // ✅ Ambil budget perusahaan
    $budget = BudgetPerusahaan::where('company', $company)
        ->where('month', $monthKey)
        ->where('year', $yearKey)
        ->first();

    $totalFuelUsed = 0;
    $totalCost = 0;

    foreach ($records as $r) {

        $fuelOut = $r->fuel_out ?? 0;
        $totalFuelUsed += $fuelOut;

        // ✅ harga fuel berdasarkan NIK
        $price = NikFuelPrice::where('nik', $r->nik)
            ->value('price_per_liter') ?? 0;

        $totalCost += ($fuelOut * $price);

        $time = Carbon::parse($r->recorded_at);

        $hourKey  = $time->format('Y-m-d H:00');
        $dayKey   = $time->format('Y-m-d');
        $monthKeyRecord = $time->format('Y-m');

        $fuelUsedPerHour[$hourKey]   =
            ($fuelUsedPerHour[$hourKey] ?? 0) + $fuelOut;

        $fuelUsedPerDay[$dayKey]     =
            ($fuelUsedPerDay[$dayKey] ?? 0) + $fuelOut;

        $fuelUsedPerMonth[$monthKeyRecord] =
            ($fuelUsedPerMonth[$monthKeyRecord] ?? 0) + $fuelOut;

        $fuelUsedAll[$r->recorded_at] = $fuelOut;
    }

    // ==============================
    // 💰 EFISIENSI & BUDGET
    // ==============================
    $budgetAmount = $budget->budget_amount ?? 0;

    // ✅ Efisiensi Baru
    $efficiency = $budgetAmount > 0
        ? round((($budgetAmount - $totalCost) / $budgetAmount) * 100, 2)
        : 0;

    // ✅ Bonus (standar sistem tambang)
    $budgetUsedPercent = $budgetAmount > 0
        ? round(($totalCost / $budgetAmount) * 100, 2)
        : 0;

    $remainingBudget = $budgetAmount - $totalCost;

    return view('analysis.index', [
        'records' => $records,
        'fuelUsedPerHour' => $fuelUsedPerHour,
        'fuelUsedPerDay' => $fuelUsedPerDay,
        'fuelUsedPerMonth' => $fuelUsedPerMonth,
        'fuelUsedAll' => $fuelUsedAll,

        'totalFuelUsed' => $totalFuelUsed,
        'totalCost' => $totalCost,

        'efficiency' => $efficiency,
        'budgetAmount' => $budgetAmount,
        'budgetUsedPercent' => $budgetUsedPercent,
        'remainingBudget' => $remainingBudget,

        'company' => $company,
        'nik' => $nik,
        'start' => $start,
        'end' => $end,
    ]);
}


  
}
