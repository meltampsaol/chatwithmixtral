<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory()->count(5)->create(); // Generates 50 dummy users
    }
}
