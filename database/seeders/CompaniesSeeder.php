<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Central Company',
                'status' => 'central',
            ],
            [
                'name' => 'Eastern Company',
                'status' => 'branch',
            ],
            [
                'name' => 'Western Company',
                'status' => 'branch',
            ],
            [
                'name' => 'Otomotive Rental Company',
                'status' => 'rental',
            ],
            [
                'name' => 'Otomotive2 Rental Company',
                'status' => 'rental',
            ],
        ];
        DB::table('companies')->insert($data);
    }
}
