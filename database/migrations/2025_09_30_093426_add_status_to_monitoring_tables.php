<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan status ke tabel perusahaan yang sudah ada
        $tables = [
            'company_perusahaan_a',
            'company_perusahaan_b',
            'company_perusahaan_c',
            // tambahin daftar lain kalau ada
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('status')->default('normal')->after('fuel_out');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'company_perusahaan_a',
            'company_perusahaan_b',
            'company_perusahaan_c',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('status');
                });
            }
        }
    }
};

