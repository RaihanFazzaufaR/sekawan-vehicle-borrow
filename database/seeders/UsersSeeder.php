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
        $data = 
        [ 
            [
                'username' => 'Admin1',
                'password' => bcrypt('password123'),
                'full_name' => 'Central Admin',
                'role' => 'admin',
                'company_id' => 1,
                'level' => 0,
            ],
            [
                'username' => 'Admin2',
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Admin',
                'role' => 'admin',
                'company_id' => 2,
                'level' => 0,
            ],
            [
                'username' => 'Admin3',
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Admin',
                'role' => 'admin',
                'company_id' => 3,
                'level' => 0,
            ],
            [
                'username' => 'HeadEast',
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Company Head',
                'role' => 'approver',
                'company_id' => 2,
                'level' => 2,
            ],
            [
                'username' => 'HeadWest',
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Company Head',
                'role' => 'approver',
                'company_id' => 3,
                'level' => 2,
            ],
            [
                'username' => 'HeadCentral',
                'password' => bcrypt('password123'),
                'full_name' => 'Central Company Head',
                'role' => 'approver',
                'company_id' => 1,
                'level' => 2,
            ],
            [
                'username' => 'SupervisorEast',
                'password' => bcrypt('password123'),
                'full_name' => 'Eastern Branch Supervisor',
                'role' => 'approver',
                'company_id' => 2,
                'level' => 1,
            ],
            [
                'username' => 'SupervisorWest',
                'password' => bcrypt('password123'),
                'full_name' => 'Western Branch Supervisor',
                'role' => 'approver',
                'company_id' => 3,
                'level' => 1,
            ],
            [
                'username' => 'SupervisorCentral',
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
