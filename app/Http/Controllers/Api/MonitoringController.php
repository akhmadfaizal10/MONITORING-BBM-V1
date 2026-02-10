<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\TableManager;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. VALIDASI DATA MASUK
        |--------------------------------------------------------------------------
        | fuel_level = DATA SENSOR MENTAH
        */
        $data = $request->validate([
            'company'     => 'required|string',
            'nik'         => 'required|string',
            'vehicle_id'  => 'required|string',
            'fuel_level'  => 'required|numeric', // SENSOR MENTAH
            'recorded_at' => 'nullable|date',
            'location'    => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL KALIBRASI BERDASARKAN NIK
        |--------------------------------------------------------------------------
        */
        $calibration = DB::table('vehicle_calibrations')
            ->where('nik', $data['nik'])
            ->first();

        if (!$calibration) {
            return response()->json([
                'message' => 'Kalibrasi untuk NIK ini belum tersedia'
            ], 422);
        }

        $sensorKosong   = $calibration->sensor_kosong;
        $faktorPerLiter = $calibration->faktor_per_liter;

        /*
        |--------------------------------------------------------------------------
        | 3. KONVERSI SENSOR → LITER
        |--------------------------------------------------------------------------
        */
        $nilaiSensor = $data['fuel_level'];

        // Proteksi nilai tidak logis
        if ($nilaiSensor < $sensorKosong) {
            $nilaiSensor = $sensorKosong;
        }

        $data['fuel_level'] = round(
            ($nilaiSensor - $sensorKosong) / $faktorPerLiter,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 4. SIAPKAN TABEL DINAMIS
        |--------------------------------------------------------------------------
        */
        $companyTable = TableManager::ensureCompanyTable($data['company']);
        $nikTable     = TableManager::ensureNikTable($data['company'], $data['nik']);

        /*
        |--------------------------------------------------------------------------
        | 5. WAKTU & LOKASI
        |--------------------------------------------------------------------------
        */
        $location   = $data['location'] ?? null;
       $recordedAt = isset($data['recorded_at'])
    ? Carbon::parse($data['recorded_at'])
    : Carbon::now();


        /*
        |--------------------------------------------------------------------------
        | 6. HITUNG FUEL IN / FUEL OUT
        |--------------------------------------------------------------------------
        */
        $fuelIn  = 0;
        $fuelOut = 0;

        $lastRecord = DB::table($nikTable)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($lastRecord) {
            $selisih = $data['fuel_level'] - $lastRecord->fuel_level;

            if ($selisih > 0) {
                $fuelIn = round($selisih, 2);
            } elseif ($selisih < 0) {
                $fuelOut = round(abs($selisih), 2);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. PREDIKSI STATUS (PYTHON SERVICE)
        |--------------------------------------------------------------------------
        */
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
            $status = 'error';
        }

        /*
        |--------------------------------------------------------------------------
        | 8. INSERT KE TABEL PERUSAHAAN
        |--------------------------------------------------------------------------
        */
        DB::table($companyTable)->insert([
            'nik'         => $data['nik'],
            'vehicle_id'  => $data['vehicle_id'],
            'fuel_level'  => $data['fuel_level'],
            'fuel_in'     => $fuelIn,
            'fuel_out'    => $fuelOut,
            'status'      => $status,
            'location'    => $location,
            'recorded_at' => $recordedAt,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 9. INSERT KE TABEL KENDARAAN (HISTORY DETAIL)
        |--------------------------------------------------------------------------
        */
        $id = DB::table($nikTable)->insertGetId([
            'nik'         => $data['nik'],
            'vehicle_id'  => $data['vehicle_id'],
            'fuel_level'  => $data['fuel_level'],
            'fuel_in'     => $fuelIn,
            'fuel_out'    => $fuelOut,
            'status'      => $status,
            'location'    => $location,
            'recorded_at' => $recordedAt,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 10. RESPONSE API
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'message'      => 'Data berhasil disimpan',
            'id'           => $id,
            'fuel_level'   => $data['fuel_level'],
            'fuel_in'      => $fuelIn,
            'fuel_out'     => $fuelOut,
            'status'       => $status,
            'recorded_at'  => $recordedAt,
        ], 201);
    }
}
