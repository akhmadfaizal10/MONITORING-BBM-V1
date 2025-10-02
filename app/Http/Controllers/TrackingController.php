<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TableManager;

class TrackingController extends Controller
{
  public function index()
{
    $companies = DB::select("SHOW TABLES LIKE 'company_%'");
    $vehicles = [];

    foreach ($companies as $company) {
        $tableName = array_values((array) $company)[0];

        $records = DB::table($tableName)
            ->select('nik', 'vehicle_id', 'status', 'fuel_level', 'recorded_at')
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('nik');

        foreach ($records as $nik => $group) {
            $latest = $group->first();
            $vehicles[$tableName][] = [
                'nik'        => $latest->nik,
                'vehicle_id' => $latest->vehicle_id,
                'status'     => $latest->status,
                'fuel_level' => $latest->fuel_level,
                'recorded_at'=> $latest->recorded_at,
            ];
        }
    }

    return view('tracking', compact('vehicles'));
}

}
