<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Hot Coffee',
            'Iced Coffee',
            'Tea',
            'Pastries',
            'Sandwiches',
            'Desserts',
            'Snacks',
            'Non-Coffee Beverages',
        ];

        return [
            'name' => $name = fake()->unique()->randomElement($categories),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_visible' => fake()->boolean(),
            'created_at' => fake()->dateTimeBetween('-1 year', '-6 month'),
            'updated_at' => fake()->dateTimeBetween('-5 month'),
        ];
    }
}
