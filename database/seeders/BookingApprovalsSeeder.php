<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class BookingApprovalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $users = DB::table('users')
            ->select('id', 'level', 'company_id')
            ->get()
            ->groupBy('company_id');

        $bookings = DB::table('bookings')
            ->join('staff', 'bookings.booker_id', '=', 'staff.staff_id')
            ->select('bookings.booking_id', 'bookings.booker_id', 'bookings.status', 'staff.company_id')
            ->orderBy('bookings.booking_id')
            ->get();

        $data = [];
        foreach ($bookings as $booking) {
            $companyUsers = $users->get($booking->company_id, collect());
            $approversLevel1 = $companyUsers->where('level', 1);
            $approversLevel2 = $companyUsers->where('level', 2);

            if ($approversLevel1->isEmpty() || $approversLevel2->isEmpty()) {
                continue; // Skip if no approvers found for both levels
            }

            $approverLevel1 = $approversLevel1->random();
            $approverLevel2 = $approversLevel2->random();

            $statusLevel1 = 'pending';
            $statusLevel2 = 'pending';

            if ($booking->status === 'approved') {
                $statusLevel1 = 'approved';
                $statusLevel2 = 'approved';
            } elseif ($booking->status === 'rejected') {
                $statusLevel1 = $faker->randomElement(['rejected', 'approved']);
                $statusLevel2 = $statusLevel1 === 'rejected' ? 'rejected' : 'rejected';
            } elseif ($booking->status === 'pending') {
                $statusLevel1 = $faker->randomElement(['pending', 'approved']);
                $statusLevel2 = $statusLevel1 === 'pending' ? 'pending' : 'pending';
            }

            $data[] = [
                'booking_id' => $booking->booking_id,
                'approver_id' => $approverLevel1->id,
                'approval_level' => 1,
                'status' => $statusLevel1,
                'approved_at' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            ];

            $data[] = [
                'booking_id' => $booking->booking_id,
                'approver_id' => $approverLevel2->id,
                'approval_level' => 2,
                'status' => $statusLevel2,
                'approved_at' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('booking_approvals')->insert($data);
    }
}
