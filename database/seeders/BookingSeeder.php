<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $userIds = DB::table('users')
            ->where('level', 0)
            ->pluck('id')
            ->toArray();

        $staffIds = DB::table('staff')
            ->pluck('staff_id')
            ->toArray();

        $vehicleIds = DB::table('vehicles')
            ->where('status', 'available')
            ->pluck('vehicle_id')
            ->toArray();

        shuffle($vehicleIds);

        if (count($vehicleIds) > 15) {
            $vehicleIds = array_slice($vehicleIds, 0, 15);
        }

        $numOfBookings = count($vehicleIds);

        $companiesId = User::where('role', 'admin')->pluck('company_id')->toArray();
        $adminsCompanyStaff = array_filter(Staff::all()->toArray(), fn($record) => in_array($record['company_id'], $companiesId));
        $drivers = array_filter(Driver::all()->toArray(), fn($record) => in_array($record['staff_id'], array_column($adminsCompanyStaff, 'staff_id')));

        for ($i = 0; $i < $numOfBookings; $i++) {
            foreach ($companiesId as $companyId) {
                $selectedStaffIds = $faker->randomElement(array_filter($adminsCompanyStaff, fn($record) => $record['company_id'] == $companyId));
                $selectedDriver = $faker->randomElement(
                    Driver::where('staff_id', $selectedStaffIds['staff_id'])->get()->toArray(),
                );
                // dd($selectedDriver);

                if (!$selectedDriver) continue;

                Booking::create([
                    'booker_id' => $selectedStaffIds['staff_id'],
                    'admin_id' => User::where('company_id', $companyId)->where('role', 'admin')->first()->id,
                    'driver_id' => $selectedDriver['driver_id'],
                    'vehicle_id' => $faker->randomElement($vehicleIds),
                    'start_date' => $faker->dateTimeBetween('-1 month', 'now'),
                    'end_date' => $faker->dateTimeBetween('now', '+1 month'),
                    'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                    // 'company_id' => $companyId,
                ]);
            }
        }

        //     foreach ($vehicleIds as $vehicleId) {
        //         $bookerId = $faker->randomElement($staffIds);

        //         DB::table('bookings')->insert([
        //             'booker_id' => $bookerId,
        //             'admin_id' => $faker->randomElement($userIds),
        //             'driver_id' => $faker->randomElement($staffIds),
        //             'vehicle_id' => $vehicleId,
        //             'start_date' => $faker->dateTimeBetween('-1 month', 'now'),
        //             'end_date' => $faker->dateTimeBetween('now', '+1 month'),
        //             'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
        //             'company_id' => $companyId,
        //         ]);
        //     }

        //     $approvedDriverIds = DB::table('bookings')
        //         ->where('status', 'approved')
        //         ->where('end_date', '<', now())
        //         ->pluck('driver_id')
        //         ->toArray();

        //     DB::table('drivers')
        //         ->whereIn('driver_id', $approvedDriverIds)
        //         ->update(['status' => 'available']);

        //     $approvedVehicleIds = DB::table('bookings')
        //         ->where('status', 'approved')
        //         ->where('end_date', '<', now())
        //         ->pluck('vehicle_id')
        //         ->toArray();

        //     DB::table('vehicles')
        //         ->whereIn('vehicle_id', $approvedVehicleIds)
        //         ->update(['status' => 'available']);
    }
}
