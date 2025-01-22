<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
          'title' => $this->faker->company,
            'user_id' => User::factory(),
            'description' => $this->faker->paragraph,
            'address' => $this->faker->address,
            'country'=> $this->faker->company,
            'currency'=>'$',
        ];
    }
}
