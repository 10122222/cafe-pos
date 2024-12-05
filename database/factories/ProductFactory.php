<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Espresso',
            'Cappuccino',
            'Café Latte',
            'Americano',
            'Mocha',
            'Green Tea Latte',
            'Croissant',
            'Chocolate Muffin',
            'Cheesecake',
            'Iced Caramel Macchiato',
            'Club Sandwich',
            'Chicken Wrap',
            'Chocolate Brownie',
        ];

        return [
            'name' => $name = fake()->unique()->randomElement($products),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_visible' => fake()->boolean(),
            'price' => fake()->numberBetween(25000, 75000),
            'created_at' => fake()->dateTimeBetween('-1 year', '-6 month'),
            'updated_at' => fake()->dateTimeBetween('-5 month'),
        ];
    }
}
