<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TableManager;
use Carbon\Carbon;

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
            return view('analysis', [
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
            return view('analysis', [
                'message' => "Tidak ada data dari $start sampai $end",
                'company' => $company,
                'start' => $start,
                'end' => $end,
                'nik' => $nik
            ]);
        }

        // ==============================
        // 📊 Hitung Data
        // ==============================
        $fuelUsedPerHour = [];
        $fuelUsedPerDay = [];
        $fuelUsedPerMonth = [];
        $fuelUsedAll = []; // ✅ untuk chart “Semua Data”

        $costPerLiter = 13500;
        $totalFuelUsed = 0;

        foreach ($records as $r) {
            $fuelOut = $r->fuel_out ?? 0;
            $totalFuelUsed += $fuelOut;

            $hourKey  = Carbon::parse($r->recorded_at)->format('Y-m-d H:00');
            $dayKey   = Carbon::parse($r->recorded_at)->format('Y-m-d');
            $monthKey = Carbon::parse($r->recorded_at)->format('Y-m');

            $fuelUsedPerHour[$hourKey]   = ($fuelUsedPerHour[$hourKey] ?? 0) + $fuelOut;
            $fuelUsedPerDay[$dayKey]     = ($fuelUsedPerDay[$dayKey] ?? 0) + $fuelOut;
            $fuelUsedPerMonth[$monthKey] = ($fuelUsedPerMonth[$monthKey] ?? 0) + $fuelOut;

            // semua data (langsung urut sesuai waktu)
            $fuelUsedAll[$r->recorded_at] = $fuelOut;
        }

        // 🔹 Biaya & Efisiensi
        $totalCost = $totalFuelUsed * $costPerLiter;
        $avgFuelLevel = $records->avg('fuel_level');
        $efficiency = $avgFuelLevel > 0
            ? round(($totalFuelUsed / $avgFuelLevel) * 100, 2)
            : 0;

        return view('analysis.index', [
            'records' => $records,
            'fuelUsedPerHour' => $fuelUsedPerHour,
            'fuelUsedPerDay' => $fuelUsedPerDay,
            'fuelUsedPerMonth' => $fuelUsedPerMonth,
            'fuelUsedAll' => $fuelUsedAll, // ✅ untuk chart semua data
            'totalFuelUsed' => $totalFuelUsed,
            'totalCost' => $totalCost,
            'efficiency' => $efficiency,
            'company' => $company,
            'nik' => $nik,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
