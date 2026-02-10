<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('nik_fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('nik');
            $table->integer('price_per_liter');
            $table->timestamps();

            $table->unique(['company','nik']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nik_fuel_prices');
    }
};
