<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class VehicleMaintenanceScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $vehicleIds = DB::table('vehicles')
            ->whereIn('status', ['available', 'maintenance'])
            ->pluck('vehicle_id')
            ->toArray();

        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $data[] = [
                'vehicle_id' => $faker->randomElement($vehicleIds),
                'date' => $faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
                'description' => $faker->sentence(),
                'status' => $faker->randomElement(['scheduled', 'completed']),
            ];
        }
    }
}
