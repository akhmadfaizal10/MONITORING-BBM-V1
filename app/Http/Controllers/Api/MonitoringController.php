<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TableManager;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function store(Request $request)
    {
        // Validasi hanya 4 field utama
        $data = $request->validate([
            'company'    => 'required|string',
            'nik'        => 'required|string',
            'vehicle_id' => 'required|string',
            'fuel_level' => 'required|numeric', // liter
            'recorded_at' => 'nullable|date',   // opsional
            'location'    => 'nullable|string', // opsional
        ]);

        // Buat tabel perusahaan & kendaraan
        $companyTable = TableManager::ensureCompanyTable($data['company']);
        $nikTable     = TableManager::ensureNikTable($data['company'], $data['nik']);

        // Lokasi & waktu otomatis
        $location   = $data['location'] ?? null;
        $recordedAt = isset($data['recorded_at'])
            ? Carbon::parse($data['recorded_at'])
            : Carbon::now();

        // Ambil semua record dalam 1 menit terakhir
        $lastMinuteRecords = DB::table($nikTable)
            ->whereBetween('recorded_at', [
                $recordedAt->copy()->subMinute()->startOfMinute(),
                $recordedAt->copy()->subMinute()->endOfMinute()
            ])
            ->orderBy('recorded_at', 'asc')
            ->get();
$fuelIn = 0;
$fuelOut = 0;

// Ambil record terakhir (bukan per menit, tapi bener-bener terakhir saja)
$lastRecord = DB::table($nikTable)
    ->orderBy('recorded_at', 'desc')
    ->first();

if ($lastRecord) {
    $selisih = $data['fuel_level'] - $lastRecord->fuel_level;

    if ($selisih > 0) {
        $fuelIn = $selisih;   // berarti pengisian BBM
    } elseif ($selisih < 0) {
        $fuelOut = abs($selisih); // berarti konsumsi/pencurian
    }
}
    
       $status = 'unknown';
try {
    $response = Http::post('http://127.0.0.1:5001/predict', [
        'fuel_level' => $data['fuel_level'],
        'fuel_in'    => $fuelIn,
        'fuel_out'   => $fuelOut,
    ]);

    if ($response->successful()) {
        $status = $response->json()['prediction'] ?? 'unknown';
    }
} catch (\Exception $e) {
    $status = 'error'; // fallback kalau Python service mati
}

        // Insert ke tabel perusahaan
        DB::table($companyTable)->insert([
            'nik'         => $data['nik'],
            'vehicle_id'  => $data['vehicle_id'],
            'fuel_level'  => $data['fuel_level'],
            'fuel_in'     => $fuelIn,
            'fuel_out'    => $fuelOut,
            'status' => $status,
            'location'    => $location,
            'recorded_at' => $recordedAt,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Insert ke tabel kendaraan (detail history)
       $id = DB::table($nikTable)->insertGetId([
    'nik'         => $data['nik'],
    'vehicle_id'  => $data['vehicle_id'],
    'fuel_level'  => $data['fuel_level'],
    'fuel_in'     => $fuelIn,
    'fuel_out'    => $fuelOut,
    'status' => $status,
    'location'    => $location,
    'recorded_at' => $recordedAt,
    'created_at'  => now(),
    'updated_at'  => now(),
]);
        return response()->json([
            'message'       => 'Data berhasil disimpan',
            'company_table' => $companyTable,
            'nik_table'     => $nikTable,
            'id'            => $id,
            'fuel_in'       => $fuelIn,
            'fuel_out'      => $fuelOut,
            'status' => $status,
            'recorded_at'   => $recordedAt,
        ], 201);
    }
}
