<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TableManager
{
    public static function getCompanyTable($company)
    {
        return "company_" . strtolower(str_replace(' ', '_', $company));
    }

    public static function getNikTable($company, $nik)
    {
        return self::getCompanyTable($company) . "_" . strtolower($nik);
    }

    public static function ensureCompanyTable($company)
    {
        $table = self::getCompanyTable($company);

        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('nik');                  // NIK kendaraan
                $t->string('vehicle_id');           // ID kendaraan unik
                $t->float('fuel_level');            // level BBM (liter)
                $t->float('fuel_in')->default(0);   // pengisian (liter)
                $t->float('fuel_out')->default(0);  // konsumsi (liter)
                $t->string('status')->default('normal'); // hasil deteksi ML
                $t->string('location')->nullable(); // lokasi GPS / area
                $t->timestamp('recorded_at');       // waktu data tercatat
                $t->timestamps();                   // created_at, updated_at
            });
        } else {
            // sync kolom wajib
            self::syncCompanyColumns($table);
        }

        return $table;
    }

    public static function ensureNikTable($company, $nik)
    {
        $table = self::getNikTable($company, $nik);

        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('nik');                  // NIK kendaraan
                $t->string('vehicle_id');           // ID kendaraan unik
                $t->float('fuel_level');            // level BBM (liter)
                $t->float('fuel_in')->default(0);   // pengisian (liter)
                $t->float('fuel_out')->default(0);  // konsumsi (liter)
                $t->string('status')->default('normal'); // hasil deteksi ML
                $t->string('location')->nullable(); // lokasi GPS / area
                $t->timestamp('recorded_at');       // waktu data tercatat
                $t->timestamps();                   // created_at, updated_at
            });
        } else {
            // sync kolom wajib
            self::syncNikColumns($table);
        }

        return $table;
    }

    // Pastikan tabel company selalu punya kolom wajib
    protected static function syncCompanyColumns($table)
    {
        if (!Schema::hasColumn($table, 'nik')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('nik')->after('id'));
        }
        if (!Schema::hasColumn($table, 'vehicle_id')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('vehicle_id')->after('nik'));
        }
        if (!Schema::hasColumn($table, 'status')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('status')->default('normal')->after('fuel_out'));
        }
    }

    // Pastikan tabel nik selalu punya kolom wajib
    protected static function syncNikColumns($table)
    {
        if (!Schema::hasColumn($table, 'nik')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('nik')->after('id'));
        }
        if (!Schema::hasColumn($table, 'vehicle_id')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('vehicle_id')->after('nik'));
        }
        if (!Schema::hasColumn($table, 'status')) {
            Schema::table($table, fn(Blueprint $t) => $t->string('status')->default('normal')->after('fuel_out'));
        }
    }
}
