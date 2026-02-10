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

        // 🔹 Lokasi utama per perusahaan beserta longitude dan latitude
        $locations = [
            'Perusahaan Tambang A' => ['longitude' => 116.8312, 'latitude' => -1.2660],
            'Perusahaan Tambang B' => ['longitude' => 116.8312, 'latitude' => -1.2660],
            'Perusahaan Tambang C' => ['longitude' => 116.8312, 'latitude' => -1.2660],
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

        // 🔹 Periode waktu yang diminta
        $periods = [
            ['year' => 2025, 'month' => 12, 'startDay' => 1, 'endDay' => 31],
            ['year' => 2026, 'month' => 1,  'startDay' => 1, 'endDay' => 31],
            ['year' => 2026, 'month' => 2,  'startDay' => 1, 'endDay' => 8],
        ];

        foreach ($locations as $company => $location) {

            // Tiap perusahaan 2–4 kendaraan
            $vehicleCount = rand(2, 4);

            for ($v = 1; $v <= $vehicleCount; $v++) {

                $nik = 'K' . str_pad($v, 2, '0', STR_PAD_LEFT);
                $vehicleType = $faker->randomElement($vehicleTypes);
                $fuelLevel = $faker->numberBetween(300, 600);

                foreach ($periods as $p) {
                    for ($day = $p['startDay']; $day <= $p['endDay']; $day++) {

                        // Mulai jam 06:00
                        $startTime = Carbon::create(
                            $p['year'],
                            $p['month'],
                            $day,
                            6, 0, 0
                        );

                        // Sampai jam 17:00
                        $endTime = Carbon::create(
                            $p['year'],
                            $p['month'],
                            $day,
                            17, 0, 0
                        );

                        for ($time = $startTime->copy(); $time <= $endTime; $time->addMinutes(5)) {

                            $event = $faker->randomElement([
                                'normal', 'normal', 'normal', 'refuel', 'theft'
                            ]);

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

                            // Acak koordinat GPS
                            $longitude = $location['longitude'] + $faker->randomFloat(5, -0.001, 0.001);
                            $latitude  = $location['latitude']  + $faker->randomFloat(5, -0.001, 0.001);

                            $req = new Request([
                                'company'     => $company,
                                'nik'         => $nik,
                                'vehicle_id'  => $vehicleType,
                                'fuel_level'  => $fuelLevel,
                                'recorded_at' => $time->toDateTimeString(),
                                'longitude'   => $longitude,
                                'latitude'    => $latitude,
                            ]);

                            $controller->store($req);
                        }
                    }
                }
            }
        }

        echo "✅ Seeder selesai: Des 2025 – Jan 2026 – Feb 2026 (1–8), jam 06:00–17:00, interval 5 menit.\n";
    }
}
