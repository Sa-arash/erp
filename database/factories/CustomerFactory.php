<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'description' => $this->faker->paragraph,
            'company_id' => Company::factory(),
         'gender' => $this->faker->randomElement(['male','female','other']),
        ];
    }
}
