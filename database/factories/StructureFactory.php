<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Structure;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Structure>
 */
class StructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,  
            'sort' => $this->faker->optional()->randomDigitNotNull, 
            'type' => $this->faker->randomElement(['room', 'shelf', 'aisle', 'row']), 
            'location' => $this->faker->boolean, 
            // 'parent_id' => Structure::factory(),  
            'warehouse_id' => 1,  
            'company_id' => 1,  
        ];
    }
}
