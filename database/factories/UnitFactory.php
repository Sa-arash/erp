<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->randomElement([
                'Kilogram',
                'Liter',
                'Piece',
                'Box',
                'Pound',
                'Meter',
                'Dozen',
                'Gram',
                'Square Meter',
                'Cubic Meter'
            ]), 
            'is_package' => false,
            'items_per_package' => null,
            'company_id' => 1,
        ];
    }
}
