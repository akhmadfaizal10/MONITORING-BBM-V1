<?php

namespace App\Http\Controllers;

use App\Models\VehicleCalibration;
use Illuminate\Http\Request;

class VehicleCalibrationController extends Controller
{
    public function index()
    {
        $data = VehicleCalibration::all();
        return view('calibration.index', compact('data'));
    }

    public function create()
    {
        return view('calibration.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|unique:vehicle_calibrations,nik',
            'sensor_kosong' => 'required|numeric',
            'faktor_per_liter' => 'required|numeric|min:0.0001',
        ]);

        VehicleCalibration::create($request->all());

        return redirect()->route('calibration.index')
            ->with('success', 'Kalibrasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data = VehicleCalibration::findOrFail($id);
        return view('calibration.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = VehicleCalibration::findOrFail($id);

        $request->validate([
            'sensor_kosong' => 'required|numeric',
            'faktor_per_liter' => 'required|numeric|min:0.0001',
        ]);

        $data->update($request->all());

        return redirect()->route('calibration.index')
            ->with('success', 'Kalibrasi berhasil diupdate');
    }

    public function destroy($id)
    {
        VehicleCalibration::destroy($id);

        return redirect()->route('calibration.index')
            ->with('success', 'Kalibrasi dihapus');
    }
}
