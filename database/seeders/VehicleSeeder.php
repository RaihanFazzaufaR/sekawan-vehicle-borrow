<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $companyIds = DB::table('companies')
            ->whereIn('status', ['rental', 'central'])
            ->pluck('company_id')
            ->toArray();

        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $data[] = [
                'ownership_id' => $faker->randomElement($companyIds),
                'vehicle_type' => $faker->randomElement(['person', 'goods']),
                'license_plate' => $faker->unique()->regexify('[A-Z]{1,3}-[0-9]{1,4}'),
                'brand' => $faker->randomElement(['Toyota', 'Honda', 'Suzuki', 'Mitsubishi']),
                'model' => $faker->randomElement(['Avanza', 'Xenia', 'Jazz', 'Civic', 'Ertiga', 'APV']),
                'status' => 'available',
            ];
        }

        DB::table('vehicles')->insert($data);
    }
}
