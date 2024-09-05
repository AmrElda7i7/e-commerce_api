<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 1000), // Random float number between 10 and 1000
            'quantity' => $this->faker->numberBetween(1, 100), // Random number between 1 and 100
            'description' => $this->faker->sentence,
            'category_id' => Category::factory(), // Create a related category
        ];
    }
}
