<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
   protected $fillable = [
    'nik', 'company', 'vehicle_id',
    'fuel_level', 'fuel_in', 'fuel_out',
    'location', 'recorded_at'
];

}

