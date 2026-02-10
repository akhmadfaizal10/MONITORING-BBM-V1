<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NikFuelPrice extends Model
{
    protected $fillable = [
        'company',
        'nik',
        'price_per_liter'
    ];
}
