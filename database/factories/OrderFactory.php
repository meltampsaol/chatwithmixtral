<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => $this->faker->unique()->numerify('ORD###'),
            'user_id' => \App\Models\User::factory(), // Assumes a user factory exists
            'total_amount' => $this->faker->randomFloat(2, 50, 5000),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
        ];
    }
}
