<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Pastikan pengguna terautentikasi
        if (!$user || !$user->company) {
            return redirect()->route('home')->with('error', 'Unauthorized access');
        }
        
        // 1. Ambil semua nama tabel yang relevan dari database untuk perusahaan pengguna
        $tableName = $this->resolveCompanyTable($user->company);
        
        if (!$tableName) {
            return view('tracking')->with('vehicles', []);
        }

        // 2. Ambil data terbaru dari tabel kendaraan
        $latestRecords = DB::table($tableName)
            ->orderBy('recorded_at', 'asc')
            ->get();
        
        // 3. Buat array untuk menyimpan data kendaraan
        $vehicles = [];
        foreach ($latestRecords as $record) {
            $vehicles[$record->nik] = [
                'nik'         => $record->nik,
                'vehicle_id'  => $record->vehicle_id,
                'status'      => $record->status,
                'fuel_level'  => $record->fuel_level,
                'recorded_at' => $record->recorded_at,
            ];
        }

        return view('tracking-user', compact('vehicles'));
    }

    private function resolveCompanyTable($company)
    {
        if (!$company) return null;
        $formatted = strtolower(trim(str_replace(' ', '_', $company)));
        $match = DB::select("SHOW TABLES LIKE 'company_{$formatted}%'");
        return !empty($match) ? array_values((array)$match[0])[0] : null;
    }
}