<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    public function index()
    {
        // 1. Ambil semua nama tabel yang relevan dari database
        $allTables = DB::select("SHOW TABLES LIKE 'company_%'");
        
        // 2. Buat array untuk mengelompokkan tabel berdasarkan perusahaan induk
        $groupedTables = [];
        foreach ($allTables as $table) {
            $tableName = array_values((array) $table)[0];
            
            // Logika untuk menentukan nama perusahaan induk dari nama tabel
            // Contoh: "company_dinustek_k01" akan menjadi induk "Dinustek"
            // Contoh: "company_perusahaan_pro_aneka_cipta" akan menjadi induk "Perusahaan Pro Aneka Cipta"
            $strippedName = str_replace('company_', '', $tableName);
            $parts = explode('_', $strippedName);
            
            // Heuristik: Jika bagian terakhir dari nama tabel terlihat seperti kode kendaraan (cth: k01, v02),
            // kita anggap itu bukan bagian dari nama perusahaan induk.
            $lastPart = end($parts);
            if (count($parts) > 1 && preg_match('/[a-zA-Z]+\d+|\d+[a-zA-Z]+/', $lastPart)) {
                 array_pop($parts); // Hapus bagian kode kendaraan
            }
            
            $parentName = ucwords(implode(' ', $parts));
            
            // Kelompokkan nama tabel asli di bawah nama induknya
            $groupedTables[$parentName][] = $tableName;
        }
        
        // 3. Ambil data kendaraan dari tabel-tabel yang sudah dikelompokkan
        $vehicles = [];
        foreach ($groupedTables as $companyName => $tables) {
            $companyVehicles = [];
            foreach ($tables as $tableName) {
                // Ambil data terbaru dari setiap tabel
                $latestRecord = DB::table($tableName)->orderBy('recorded_at', 'desc')->first();
                
                if ($latestRecord) {
                    // Gunakan NIK sebagai kunci untuk menghindari duplikat jika ada data yang tumpang tindih
                    $companyVehicles[$latestRecord->nik] = [
                        'nik'         => $latestRecord->nik,
                        'vehicle_id'  => $latestRecord->vehicle_id,
                        'status'      => $latestRecord->status,
                        'fuel_level'  => $latestRecord->fuel_level,
                        'recorded_at' => $latestRecord->recorded_at,
                    ];
                }
            }
            // Simpan data kendaraan yang unik untuk perusahaan ini
            if (!empty($companyVehicles)) {
                 $vehicles[$companyName] = array_values($companyVehicles);
            }
        }

        return view('tracking', compact('vehicles'));
    }

    public function fetch()
{
    $allTables = DB::select("SHOW TABLES LIKE 'company_%'");
    $groupedTables = [];

    foreach ($allTables as $table) {
        $tableName = array_values((array) $table)[0];
        $strippedName = str_replace('company_', '', $tableName);
        $parts = explode('_', $strippedName);

        $lastPart = end($parts);
        if (count($parts) > 1 && preg_match('/[a-zA-Z]+\d+|\d+[a-zA-Z]+/', $lastPart)) {
            array_pop($parts);
        }

        $parentName = ucwords(implode(' ', $parts));
        $groupedTables[$parentName][] = $tableName;
    }

    $vehicles = [];

    foreach ($groupedTables as $companyName => $tables) {
        foreach ($tables as $tableName) {

            // 🔴 PENTING: ambil data TERBARU per kendaraan
            $latest = DB::table($tableName)
                ->whereIn('id', function ($q) use ($tableName) {
                    $q->select(DB::raw('MAX(id)'))
                      ->from($tableName)
                      ->groupBy('vehicle_id');
                })
                ->get();

            foreach ($latest as $row) {
                $vehicles[$companyName][] = [
                    'nik'         => $row->nik,
                    'vehicle_id'  => $row->vehicle_id,
                    'status'      => $row->status,
                    'fuel_level'  => $row->fuel_level,
                    'recorded_at' => $row->recorded_at,
                ];
            }
        }
    }

    return response()->json($vehicles);
}

}