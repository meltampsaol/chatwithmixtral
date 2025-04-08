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
        ['name' => 'Mel', 'email' => 'mel@example.com', 'department_id' => 1],
        ['name' => 'Mike', 'email' => 'mike@example.com', 'department_id' => 2],
        ['name' => 'John', 'email' => 'john@example.com', 'department_id' => 3],
    ]);
}

}
