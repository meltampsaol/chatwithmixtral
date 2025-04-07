<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class OrdersSeeder extends Seeder
{
 
    public function run(): void
    {
        \App\Models\Order::factory()->count(30)->create(); 
    }
}

