<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Typeleave>
 */
class TypeleaveFactory extends Factory
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
            'days' => $this->faker->numberBetween(1, 30),
            'description' => $this->faker->optional()->text(200),
            'is_payroll' => $this->faker->boolean(),
            'company_id' => Company::factory(),
        ];
    }
}
