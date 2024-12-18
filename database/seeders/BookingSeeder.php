<?php

namespace Database\Seeders;

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

        $driverIds = DB::table('drivers')
            ->where('status', 'available')
            ->pluck('driver_id')
            ->toArray();

        $vehicleIds = DB::table('vehicles')
            ->where('status', 'available')
            ->pluck('vehicle_id')
            ->toArray();

        shuffle($driverIds);
        shuffle($vehicleIds);

        if (count($driverIds) > 15) {
            $driverIds = array_slice($driverIds, 0, 15);
        }
        if (count($vehicleIds) > 15) {
            $vehicleIds = array_slice($vehicleIds, 0, 15);
        }

        $numOfBookings = min(count($driverIds), count($vehicleIds));

        $data = [];
        for ($i = 0; $i < $numOfBookings; $i++) {
            $bookingDate = $faker->dateTimeBetween('-3 months', 'now');
            $startDate = $faker->dateTimeBetween($bookingDate, $bookingDate->modify('+1 months'));
            $endDate = $faker->dateTimeBetween($startDate, $startDate->modify('+1 month'));

            $status = $endDate < now() ? 'approved' : $faker->randomElement(['pending', 'approved', 'rejected']);

            $data[] = [
                'admin_id' => $faker->randomElement($userIds),
                'vehicle_id' => $vehicleIds[$i],
                'driver_id' => $driverIds[$i],
                'booker_id' => $faker->randomElement($staffIds),
                'booking_date' => $bookingDate->format('Y-m-d'),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $status,
                'purpose' => $faker->randomElement([$faker->sentence(3), null]),
            ];

            if ($status === 'approved' && $endDate < now()) {
                DB::table('drivers')
                    ->where('driver_id', $driverIds[$i])
                    ->update(['status' => 'available']);

                DB::table('vehicles')
                    ->where('vehicle_id', $vehicleIds[$i])
                    ->update(['status' => 'available']);
            } elseif ($status === 'approved') {
                DB::table('drivers')
                    ->where('driver_id', $driverIds[$i])
                    ->update(['status' => 'unavailable']);

                DB::table('vehicles')
                    ->where('vehicle_id', $vehicleIds[$i])
                    ->update(['status' => 'booked']);
            }
        }

        // Sort the data by booking_date from oldest to latest
        usort($data, function ($a, $b) {
            return strtotime($a['booking_date']) - strtotime($b['booking_date']);
        });

        DB::table('bookings')->insert($data);

        $approvedDriverIds = DB::table('bookings')
            ->where('status', 'approved')
            ->where('end_date', '<', now())
            ->pluck('driver_id')
            ->toArray();

        DB::table('drivers')
            ->whereIn('driver_id', $approvedDriverIds)
            ->update(['status' => 'available']);

        $approvedVehicleIds = DB::table('bookings')
            ->where('status', 'approved')
            ->where('end_date', '<', now())
            ->pluck('vehicle_id')
            ->toArray();

        DB::table('vehicles')
            ->whereIn('vehicle_id', $approvedVehicleIds)
            ->update(['status' => 'available']);
    }
}