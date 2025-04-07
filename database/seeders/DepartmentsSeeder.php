<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentsSeeder extends Seeder
{
    public function run()
{
    DB::table('departments')->insert([
        ['name' => 'HR'],
        ['name' => 'Engineering'],
        ['name' => 'Marketing'],
    ]);
}

}
