<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeesSeeder extends Seeder
{
    public function run()
{
    DB::table('employees')->insert([
        ['name' => 'Alice', 'email' => 'alice@example.com', 'department_id' => 1],
        ['name' => 'Bob', 'email' => 'bob@example.com', 'department_id' => 2],
        ['name' => 'Charlie', 'email' => 'charlie@example.com', 'department_id' => 3],
    ]);
}

}
