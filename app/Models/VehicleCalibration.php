<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleCalibration extends Model
{
    protected $table = 'vehicle_calibrations';

    protected $fillable = [
        'nik',
        'sensor_kosong',
        'faktor_per_liter'
    ];
}
