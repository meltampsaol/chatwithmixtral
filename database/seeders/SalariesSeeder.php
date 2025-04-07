<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SalariesSeeder extends Seeder
{
    public function run()
    {
        DB::table('salaries')->insert([
            ['employee_id' => 1, 'amount' => 5000.00, 'paid_on' => '2025-03-01'],
            ['employee_id' => 2, 'amount' => 7000.00, 'paid_on' => '2025-03-01'],
            ['employee_id' => 3, 'amount' => 6000.00, 'paid_on' => '2025-03-01'],
        ]);
    }
    
}
