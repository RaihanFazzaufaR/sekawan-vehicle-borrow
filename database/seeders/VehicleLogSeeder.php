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
        $faker = Faker::create();

        // Fetch bookings where the end_date has already passed
        $bookings = DB::table('bookings')
            ->where('end_date', '<', now())
            ->get();

        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'vehicle_id' => $booking->vehicle_id,
                'booking_id' => $booking->booking_id,
                'distance' => $faker->numberBetween(50, 100),
                'fuel_consumed' => $faker->randomFloat(2, 5, 50),
            ];
        }

        // Insert the log data into the vehicle_logs table
        DB::table('vehicle_logs')->insert($data);
    }
}
