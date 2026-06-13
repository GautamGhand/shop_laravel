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
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 10, 500),
            'stock'       => fake()->numberBetween(1, 100),
            'category'    => fake()->word(),
            'image'       => fake()->imageUrl(),
            'is_active'   => true,
        ];
    }
}
