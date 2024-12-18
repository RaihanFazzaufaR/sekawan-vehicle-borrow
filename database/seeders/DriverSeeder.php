<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

use function Psy\sh;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $staffIds = DB::table('staff')
            ->pluck('staff_id')
            ->toArray();
        shuffle($staffIds);

        $driverIds = array_slice($staffIds, 0, 25);
        sort ($driverIds);

        $data = [];
        foreach ($driverIds as $driverId) {
            $data[] = [
                'staff_id' => $driverId,
                'status' => 'available',
            ];
        }

        DB::table('drivers')->insert($data);
    }
}
