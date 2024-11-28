<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'OR-' . fake()->unique()->randomNumber(6),
            'total_price' => fake()->numberBetween(20000, 200000),
            'status' => fake()->randomElement(['new', 'processing', 'completed', 'cancelled']),
            'notes' => fake()->realText(100),
            'created_at' => fake()->dateTimeBetween('-1 year'),
            'updated_at' => fake()->dateTimeBetween('-5 month'),
        ];
    }
}
