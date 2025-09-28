<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FuelRecordFactory extends Factory
{
    public function definition()
    {
        return [
            'fuel_level'  => $this->faker->numberBetween(50, 500),  // liter
            'fuel_in'     => $this->faker->randomFloat(2, 0, 50),  // liter masuk
            'fuel_out'    => $this->faker->randomFloat(2, 0, 50),  // liter keluar
            'location'    => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
