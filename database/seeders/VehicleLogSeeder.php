<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class VehicleLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleIds = DB::table('vehicles')
        ->pluck('vehicle_id')
        ->toArray();

        $faker = Faker::create();

        $data = [];
        for ($i = 0; $i < 60; $i++) {
            $data[] = [
                'vehicle_id' => $faker->randomElement($vehicleIds),
                'distance' => $faker->numberBetween(50, 100),
                'fuel_consumed' => $faker->randomFloat(2, 5, 50),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ];
        }

        // Insert the log data into the vehicle_logs table
        DB::table('vehicle_logs')->insert($data);
    }
}
