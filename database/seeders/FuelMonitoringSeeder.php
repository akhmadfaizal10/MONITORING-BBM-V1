<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Http\Controllers\Api\MonitoringController;

class FuelMonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder **calls the MonitoringController::store(Request $req)**
     * for each simulated minute record so the controller logic (fuel_in/out calc,
     * table creation, etc.) runs exactly the same as for IoT input.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $controller = new MonitoringController();

        // konfigurasi simulasi
        $companies = [
            'Perusahaan A',
            'Perusahaan B',
            'Perusahaan C'
        ];

        // Perusahaan -> 5-8 kendaraan masing-masing (atur sesuai kebutuhan)
        // Per kendaraan -> simulasikan N menit (mis. 1440 = 1 hari; set lebih besar bila butuh)
        $minutesPerVehicle = 60 * 24 * 7; // contoh: 7 hari per kendaraan (ubah kalau perlu)
        // Jika butuh ~1000+ record per kendaraan, set ke sekitar 1000.

        foreach ($companies as $company) {
            $vehicleCount = rand(5, 8);

            for ($v = 1; $v <= $vehicleCount; $v++) {
                $nik = 'K' . str_pad($v, 2, '0', STR_PAD_LEFT);
                $vehicleId = 'VEH-' . $nik;

                // tentukan kelas kendaraan & parameter konsumsi berdasarkan riset:
                // small (2-6 L/h), medium (8-25 L/h), large (25-80 L/h)
                $classRand = $faker->randomElement(['small','medium','large']);
                switch ($classRand) {
                    case 'small':
                        $hourlyConsAvg = $faker->randomFloat(2, 2, 6); // L/h
                        $tankCapacity = $faker->numberBetween(200, 400);
                        break;
                    case 'medium':
                        $hourlyConsAvg = $faker->randomFloat(2, 8, 25); // L/h
                        $tankCapacity = $faker->numberBetween(400, 700);
                        break;
                    default:
                        $hourlyConsAvg = $faker->randomFloat(2, 25, 80); // L/h
                        $tankCapacity = $faker->numberBetween(700, 1200);
                        break;
                }

                // konversi ke L/min (kita simulasikan tiap menit)
                $consumptionPerMinuteAvg = $hourlyConsAvg / 60;

                // start full-ish tank (antara 60-95% penuh)
                $fuelLevel = round($tankCapacity * $faker->randomFloat(2, 0.6, 0.95), 2);

                // atur probabilitas: aktif kerja, idle, heavy work spike
                // idleProb: kemungkinan idle di menit tertentu
                $idleProb = 0.25; // 25% idle
                $heavySpikeProb = 0.05; // 5% heavy load spike (konsumsi > avg)

                // probability of theft per vehicle per day (very low)
                $theftDailyProb = 0.002; // 0.2% per day (atur sesuai kebutuhan)

                // gunakan start time beberapa hari lalu agar recorded_at tidak semua now()
                $startTime = Carbon::now()->subMinutes($minutesPerVehicle);

                // kita track per hari theft chance
                $minutesElapsed = 0;
                for ($m = 0; $m < $minutesPerVehicle; $m++) {
                    $minutesElapsed++;
                    $currentTime = $startTime->copy()->addMinutes($m);

                    // decide if vehicle is working this minute
                    $isIdle = $faker->boolean($idleProb * 100); // true = idle
                    $isHeavy = $faker->boolean($heavySpikeProb * 100);

                    // compute fuel_out for this minute
                    if ($isIdle) {
                        $fuelOutThis = $faker->randomFloat(4, 0, max(0.01, $consumptionPerMinuteAvg * 0.2)); // tiny
                    } else {
                        // normal or heavy
                        if ($isHeavy) {
                            // heavy spike -> 1.5x - 3x consumption for this minute
                            $factor = $faker->randomFloat(2, 1.5, 3.0);
                        } else {
                            // variation around avg
                            $factor = $faker->randomFloat(2, 0.6, 1.4);
                        }
                        $fuelOutThis = round($consumptionPerMinuteAvg * $factor, 3);
                    }

                    $fuelInThis = 0;

                    // check automatic scheduled refuel possibility (if below 20% capacity)
                    if ($fuelLevel <= 0.2 * $tankCapacity && $faker->boolean(30)) { // 30% chance to refuel when low
                        // refuel to random level between 60% - 100%
                        $refillTo = $faker->randomFloat(2, 0.6, 1.0) * $tankCapacity;
                        $fuelInThis = round(max(0, $refillTo - $fuelLevel), 3);
                        $fuelLevel = $refillTo;
                    } else {
                        // normal consumption subtract
                        $fuelLevel = max(0, $fuelLevel - $fuelOutThis);
                    }

                    // theft event check: per day chance; if occurs, apply immediate sudden drop (not refuel)
                    // determine if new day started to roll theft chance per day
                    if ($minutesElapsed >= 1440) { // reset daily counter
                        $minutesElapsed = 0;
                    }
                    // approximate per-minute theft chance derived from daily prob
                    $theftPerMinuteProb = $theftDailyProb / 1440;
                    if ($faker->boolean($theftPerMinuteProb * 100)) {
                        // theft occurred: sudden withdrawal between 5% - 30% of tank (configurable)
                        $theftPct = $faker->randomFloat(2, 0.05, 0.30);
                        $theftAmount = round($tankCapacity * $theftPct, 2);
                        $fuelLevel = max(0, $fuelLevel - $theftAmount);
                        // mark as an instantaneous big fuel_out; but we want controller to detect it as out
                        $fuelOutThis += $theftAmount;
                        // OPTIONAL: you can log a separate event to a log table but controller will just compute deltas
                    }

                    // Build fake request with only 4 fields (company, nik, vehicle_id, fuel_level)
                    $req = new Request([
                        'company'    => $company,
                        'nik'        => $nik,
                        'vehicle_id' => $vehicleId,
                        'fuel_level' => round($fuelLevel, 2),
                    ]);

                    // We also want the controller to record recorded_at = now() inside controller;
                    // if you prefer recorded_at be the simulated minute time, you can adjust controller to accept 'recorded_at'
                    // For now, controller sets recorded_at = now() so the timestamps will be time of seeding.
                    // If you want recorded_at == $currentTime, you can set $req->merge(['recorded_at' => $currentTime->toDateTimeString()]);

                    // Call controller store() directly so it uses the same logic as IoT input
                    $controller->store($req);
                } // end minutes loop for one vehicle

                // small pause (optional) to avoid exhausting resources; remove if you want fastest run
                // usleep(500); 
            } // end vehicles loop
        } // end companies loop
    }
}
