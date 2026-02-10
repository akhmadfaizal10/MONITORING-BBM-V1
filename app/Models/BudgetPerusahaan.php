<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetPerusahaan extends Model
{
    // 🔥 WAJIB: arahkan ke tabel yang benar
    protected $table = 'budget_perusahaan';

    protected $fillable = [
        'company',
        'month',
        'year',
        'budget_amount',
    ];
}
