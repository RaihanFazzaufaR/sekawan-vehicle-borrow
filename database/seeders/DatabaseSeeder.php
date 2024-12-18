<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompaniesSeeder::class,
            UsersSeeder::class,
            StaffSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            VehicleMaintenanceScheduleSeeder::class,
            BookingSeeder::class,
            BookingApprovalsSeeder::class,
            VehicleLogSeeder::class,
        ]);
    }
}
