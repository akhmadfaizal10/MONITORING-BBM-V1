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

        // 🔹 Kota di Kalimantan
        $kalimantanCities = [
            'Balikpapan', 'Samarinda', 'Banjarmasin', 'Pontianak',
            'Palangkaraya', 'Tarakan', 'Tanjung Selor', 'Singkawang', 'Banjarbaru'
        ];

        // 🔹 Jenis kendaraan pertambangan
        $vehicleTypes = [
            'Dump Truck',
            'Excavator',
            'Bulldozer',
            'Fuel Truck',
            'Loader',
            'Compactor',
            'Grader',
            'Crane',
        ];

        // 🔹 Daftar perusahaan
        $companies = [
            'Perusahaan Udinus',
            'Perusahaan Pro Aneka Cipta',
            'Perusahaan Punyaku'
        ];

        foreach ($companies as $company) {
            // Tiap perusahaan 2–4 kendaraan
            $vehicleCount = rand(2, 4);

            for ($v = 1; $v <= $vehicleCount; $v++) {
                $nik = 'K' . str_pad($v, 2, '0', STR_PAD_LEFT);
                $vehicleType = $faker->randomElement($vehicleTypes);
                $fuelLevel = $faker->numberBetween(300, 600);

                // 🔹 Loop 30 hari ke belakang
                for ($day = 30; $day >= 0; $day--) {
                    $date = Carbon::now()->subDays($day)->startOfDay();

                    // 🔹 Tiap 5 menit dalam 24 jam (288 record)
                    for ($i = 0; $i < 288; $i++) {
                        $event = $faker->randomElement(['normal', 'refuel', 'theft', 'normal', 'normal']); 
                        // normal lebih sering muncul

                        if ($event === 'normal') {
                            $fuelLevel -= $faker->numberBetween(0, 2);
                        } elseif ($event === 'refuel') {
                            $fuelLevel += $faker->numberBetween(20, 150);
                        } elseif ($event === 'theft') {
                            $fuelLevel -= $faker->numberBetween(30, 100);
                        }

                        if ($fuelLevel < 0) {
                            $fuelLevel = 0;
                        }

                        $recordedAt = (clone $date)->addMinutes($i * 5);
                        $location = $faker->randomElement($kalimantanCities);

                        $req = new Request([
                            'company'     => $company,
                            'nik'         => $nik,
                            'vehicle_id'  => $vehicleType, // ⬅️ type kendaraan
                            'fuel_level'  => $fuelLevel,
                            'recorded_at' => $recordedAt->toDateTimeString(),
                            'location'    => $location,
                        ]);

                        $controller->store($req);
                    }
                }
            }
        }

        echo "✅ Seeder selesai, data sebulan penuh (interval 5 menit, vehicle_id = type kendaraan) berhasil dibuat!\n";
    }
}
