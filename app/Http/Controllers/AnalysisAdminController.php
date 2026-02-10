<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TableManager;
use Carbon\Carbon;
use App\Models\CompanyBudget;
use App\Models\NikFuelPrice;


class AnalysisAdminController extends Controller
{
   public function Index(Request $request)
{
    $user = Auth::user();

    // 🔐 Guard admin
    if ($user->role !== 'admin') {
        abort(403, 'Unauthorized');
    }

    $companies = DB::select("SHOW TABLES LIKE 'company_%'");

    $companyList = collect($companies)->map(function ($t) {
        $table = array_values((array) $t)[0];

        return ucwords(str_replace('_', ' ',
            preg_replace('/_k\d+|_v\d+$/', '', str_replace('company_', '', $table))
        ));
    })->unique()->values();

    $company = $request->input('company');

    if (!$company) {
        return view('analysis.admin.index', [
            'companyList' => $companyList,
            'message' => 'Silakan pilih perusahaan terlebih dahulu.',
            'company' => null,
            'start'   => $request->input('start', Carbon::now()->subDays(7)->startOfDay()),
            'end'     => $request->input('end', Carbon::now()->endOfDay()),
            'nik'     => null,

            'fuelUsedPerHour'  => [],
            'fuelUsedPerDay'   => [],
            'fuelUsedPerMonth' => [],
            'fuelUsedAll'      => [],
            'totalFuelUsed'    => 0,
            'totalCost'        => 0,
            'efficiency'       => 0,
        ]);
    }

    // ==========================
    // FILTER PARAMETER
    // ==========================
    $start = $request->input('start', Carbon::now()->subDays(7)->startOfDay());
    $end   = $request->input('end', Carbon::now()->endOfDay());
    $nik   = $request->input('nik');

    // ==========================
    // AMBIL TABLE
    // ==========================
    $table = $nik
        ? TableManager::getNikTable($company, $nik)
        : TableManager::getCompanyTable($company);

    if (!DB::getSchemaBuilder()->hasTable($table)) {
        return view('analysis.admin.index', [
            'companyList' => $companyList,
            'message' => "Tabel $table belum memiliki data.",
            'company' => $company,
            'start' => $start,
            'end' => $end,
            'nik' => $nik
        ]);
    }

    // ==========================
    // AMBIL DATA
    // ==========================
    $records = DB::table($table)
        ->whereBetween('recorded_at', [$start, $end])
        ->orderBy('recorded_at', 'asc')
        ->get();

    if ($records->isEmpty()) {
        return view('analysis.admin.index', [
            'companyList' => $companyList,
            'message' => "Tidak ada data dari $start sampai $end",
            'company' => $company,
            'start' => $start,
            'end' => $end,
            'nik' => $nik
        ]);
    }

    // ==============================
    // 📊 HITUNG DATA (UPDATED LOGIC)
    // ==============================
    $fuelUsedPerHour  = [];
    $fuelUsedPerDay   = [];
    $fuelUsedPerMonth = [];
    $fuelUsedAll      = [];

    // ✅ Ambil bulan & tahun dari filter
    $monthKey = Carbon::parse($start)->format('Y-m');
    $yearKey  = Carbon::parse($start)->format('Y');

    // ✅ Ambil budget perusahaan
    $budget = CompanyBudget::where('company', $company)
        ->where('month', $monthKey)
        ->where('year', $yearKey)
        ->first();

    $totalFuelUsed = 0;
    $totalCost = 0;

    foreach ($records as $r) {

        $fuelOut = $r->fuel_out ?? 0;
        $totalFuelUsed += $fuelOut;

        // ✅ harga berdasarkan NIK
        $price = NikFuelPrice::where('nik', $r->nik)
            ->value('price_per_liter') ?? 0;

        $totalCost += ($fuelOut * $price);

        $time = Carbon::parse($r->recorded_at);

        $hourKey  = $time->format('Y-m-d H:00');
        $dayKey   = $time->format('Y-m-d');
        $monthKeyLoop = $time->format('Y-m');

        $fuelUsedPerHour[$hourKey]   = ($fuelUsedPerHour[$hourKey] ?? 0) + $fuelOut;
        $fuelUsedPerDay[$dayKey]     = ($fuelUsedPerDay[$dayKey] ?? 0) + $fuelOut;
        $fuelUsedPerMonth[$monthKeyLoop] = ($fuelUsedPerMonth[$monthKeyLoop] ?? 0) + $fuelOut;
        $fuelUsedAll[$r->recorded_at] = $fuelOut;
    }

    // ==============================
    // 📈 EFISIENSI BERDASARKAN BUDGET
    // ==============================
    $budgetAmount = $budget->budget_amount ?? 0;

    $efficiency = $budgetAmount > 0
        ? round((($budgetAmount - $totalCost) / $budgetAmount) * 100, 2)
        : 0;

    // ✅ BONUS METRIC (Tambang Standard)
    $budgetUsedPercent = $budgetAmount > 0
        ? round(($totalCost / $budgetAmount) * 100, 2)
        : 0;

    $remainingBudget = $budgetAmount - $totalCost;

    return view('analysis.admin.index', [
        'companyList' => $companyList,
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
