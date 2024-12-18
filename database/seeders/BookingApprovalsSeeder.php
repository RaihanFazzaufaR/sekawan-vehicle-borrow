<?php

namespace Database\Seeders;

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
            ->select('id', 'role', 'company_id', 'full_name')
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
            $approversLevel1 = $companyUsers->where('role', 'approver')->filter(function ($user) {
                return str_contains($user->full_name, 'Supervisor');
            });
            $approversLevel2 = $companyUsers->where('role', 'approver')->filter(function ($user) {
                return str_contains($user->full_name, 'Head');
            });

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
                $statusLevel1 = 'rejected';
                $statusLevel2 = 'rejected';
            }

            $data[] = [
                'booking_id' => $booking->booking_id,
                'approver_id' => $approverLevel1->id,
                'approval_level' => 1,
                'status' => $statusLevel1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $data[] = [
                'booking_id' => $booking->booking_id,
                'approver_id' => $approverLevel2->id,
                'approval_level' => 2,
                'status' => $statusLevel2,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('booking_approvals')->insert($data);
    }
}
