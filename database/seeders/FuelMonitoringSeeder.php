<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Http\Controllers\Api\MonitoringController;

class FuelMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $controller = new MonitoringController();

        // 🔹 Kota-kota di Kalimantan saja
        $kalimantanCities = [
            'Balikpapan', 'Samarinda', 'Banjarmasin', 'Pontianak',
            'Palangkaraya', 'Tarakan', 'Tanjung Selor', 'Singkawang', 'Banjarbaru'
        ];

        $companies = [
            'Perusahaan Udinus',
            'Perusahaan Pro Aneka Cipta',
            'Perusahaan Punyaku'
        ];

        foreach ($companies as $company) {
            foreach (['K01', 'K02'] as $nik) {
                $vehicleId = "VEH-" . strtoupper($nik);
                $fuelLevel = $faker->numberBetween(200, 500);

                for ($i = 0; $i < 50; $i++) {
                    $event = $faker->randomElement(['normal', 'refuel', 'theft']);

                    if ($event === 'normal') {
                        $fuelLevel -= $faker->numberBetween(1, 5);
                    } elseif ($event === 'refuel') {
                        $fuelLevel += $faker->numberBetween(20, 100);
                    } elseif ($event === 'theft') {
                        $fuelLevel -= $faker->numberBetween(50, 150);
                    }

                    if ($fuelLevel < 0) {
                        $fuelLevel = 0;
                    }

                    $recordedAt = Carbon::now()->subMinutes(50 - $i);

                    // 🔹 Lokasi hanya kota di Kalimantan
                    $location = $faker->randomElement($kalimantanCities);

                    $req = new Request([
                        'company'    => $company,
                        'nik'        => $nik,
                        'vehicle_id' => $vehicleId,
                        'fuel_level' => $fuelLevel,
                        'recorded_at'=> $recordedAt->toDateTimeString(),
                        'location'   => $location,
                    ]);

                    $controller->store($req);
                }
            }
        }

        echo "✅ Seeder selesai, data monitoring berhasil dibuat (lokasi Kalimantan).\n";
    }
}
