<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('vehicle_calibrations', function (Blueprint $table) {
        $table->id();
        $table->string('nik')->unique();
        $table->double('sensor_kosong');
        $table->double('faktor_per_liter');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_calibrations');
    }
};
