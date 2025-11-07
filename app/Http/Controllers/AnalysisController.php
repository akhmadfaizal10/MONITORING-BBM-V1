<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TableManager;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Kalau admin, bisa pilih company
        $selectedCompany = $request->input('company', $user->company);

        // Ambil data kendaraan
        $vehicles = $this->getVehicleData($user, $selectedCompany);

        // Analisis data BBM
        $fuelData = $this->analyzeFuelData($vehicles, $user);

        // Ambil data tren berdasarkan tanggal dan company
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $fuelData['trend_data'] = $this->getTrendData($vehicles, $selectedCompany, $startDate, $endDate);

        // Ambil semua company kalau admin
        $companies = $user->role === 'admin' ? $this->getAllCompanies() : [];

        return view('analysis.index', compact('fuelData', 'companies', 'selectedCompany'));
    }

    private function getAllCompanies()
    {
        // Ambil semua tabel yang prefix-nya "company_"
        $tables = DB::getSchemaBuilder()->getAllTables();
        $companies = [];

        foreach ($tables as $tableObj) {
            $tableName = is_array($tableObj) ? array_values($tableObj)[0] : $tableObj->name ?? $tableObj;
            if (strpos($tableName, 'company_') === 0) {
                $companies[] = str_replace('company_', '', $tableName);
            }
        }

        return $companies;
    }

    private function getVehicleData($user, $selectedCompany)
    {
        if ($user->role === 'admin') {
            return $this->getVehiclesByCompany($selectedCompany);
        }

        return $this->getVehiclesByCompany($user->company);
    }

    private function getVehiclesByCompany($company)
    {
        $table = TableManager::getCompanyTable($company);
        return DB::table($table)->get();
    }

    private function analyzeFuelData($vehicles, $user)
    {
        $analysis = [
            'average_consumption' => [],
            'fuel_efficiency' => [],
            'cost_analysis' => [],
        ];

        foreach ($vehicles as $vehicle) {
            $fuelOut = $vehicle->fuel_out ?? 0;
            $fuelLevel = $vehicle->fuel_level ?? 0;

            $averageConsumption = $fuelOut > 0 ? $fuelOut / 1 : 0;
            $efficiency = ($fuelLevel > 0) ? $fuelOut / $fuelLevel : 0;
            $cost = $fuelOut * 13000;

            $analysis['average_consumption'][$vehicle->nik] = $averageConsumption;
            $analysis['fuel_efficiency'][$vehicle->nik] = $efficiency;
            $analysis['cost_analysis'][$vehicle->nik] = $cost;
        }

        return $analysis;
    }

    private function getTrendData($vehicles, $company, $startDate = null, $endDate = null)
    {
        $trendData = [];

        foreach ($vehicles as $vehicle) {
            $query = DB::table(TableManager::getCompanyTable($company))
                ->where('nik', $vehicle->nik);

            if ($startDate && $endDate) {
                $query->whereBetween('recorded_at', [$startDate, $endDate]);
            }

            $records = $query->get();

            foreach ($records as $record) {
                $dateTime = Carbon::parse($record->recorded_at)->format('Y-m-d H:i');

                if (!isset($trendData[$vehicle->nik])) {
                    $trendData[$vehicle->nik] = [
                        'nik' => $vehicle->nik,
                        'data' => []
                    ];
                }

                $trendData[$vehicle->nik]['data'][$dateTime] = $record->fuel_level;
            }
        }

        $formattedData = [];
        foreach ($trendData as $vehicle) {
            foreach ($vehicle['data'] as $dateTime => $level) {
                if (!isset($formattedData[$dateTime])) {
                    $formattedData[$dateTime] = ['date' => $dateTime];
                }
                $formattedData[$dateTime][$vehicle['nik']] = $level;
            }
        }

        return array_values($formattedData);
    }
}
