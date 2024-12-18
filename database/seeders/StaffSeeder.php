<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $companyIds = DB::table('companies')
            ->whereIn('status', ['central', 'branch'])
            ->pluck('company_id')
            ->toArray();

        $data = [];
        for ($i = 0; $i < 50; $i++) {
            $data[] = [
                'company_id' => $faker->randomElement($companyIds),
                'full_name' => $faker->firstName() . ' ' . $faker->lastName(),
                'phone_number' => $faker->phoneNumber(),
            ];
        }

        DB::table('staff')->insert($data);

    }
}
