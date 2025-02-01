<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Laptop', 'Phone', 'Tablet', 'Headphones', 'Keyboard', 
            'Mouse', 'Monitor', 'Camera', 'Smartwatch', 'Speakers'
        ];
        return [
            'title' => $this->faker->randomElement($titles),
            'image' => $this->faker->imageUrl(),
            'product_type' => $this->faker->randomElement(['consumable', 'unConsumable']),
            'stock_alert_threshold' => $this->faker->numberBetween(1, 10),
            'sku' => $this->faker->unique()->numberBetween(100000, 999999), 
            'description' => $this->faker->sentence,
            'account_id' => 7,  
            'sub_account_id' => 10,
            'company_id' => 1, 
        ];
    }
}
