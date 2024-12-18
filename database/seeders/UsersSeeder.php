<?php

namespace Database\Seeders;

use Dflydev\DotAccessData\Data;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $data = 
        [ 
            [
                'name' => 'Admin1',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Central Admin',
                'role' => 'admin',
                'company_id' => 1,
                'level' => 0,
            ],
            [
                'name' => 'Admin2',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Admin',
                'role' => 'admin',
                'company_id' => 2,
                'level' => 0,
            ],
            [
                'name' => 'Admin3',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Admin',
                'role' => 'admin',
                'company_id' => 3,
                'level' => 0,
            ],
            [
                'name' => 'HeadEast',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Company Head',
                'role' => 'approver',
                'company_id' => 2,
                'level' => 2,
            ],
            [
                'name' => 'HeadWest',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Company Head',
                'role' => 'approver',
                'company_id' => 3,
                'level' => 2,
            ],
            [
                'name' => 'HeadCentral',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Central Company Head',
                'role' => 'approver',
                'company_id' => 1,
                'level' => 2,
            ],
            [
                'name' => 'SupervisorEast',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Supervisor',
                'role' => 'approver',
                'company_id' => 2,
                'level' => 1,
            ],
            [
                'name' => 'SupervisorWest',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Supervisor',
                'role' => 'approver',
                'company_id' => 3,
                'level' => 1,
            ],
            [
                'name' => 'SupervisorCentral',
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password123'),
                'full_name' => 'Central Company Supervisor',
                'role' => 'approver',
                'company_id' => 1,
                'level' => 1,
            ]
            
        ];
        DB::table('users')->insert($data);
    }
    
}
