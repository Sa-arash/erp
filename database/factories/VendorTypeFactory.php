<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorType>
 */
class VendorTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
          'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'company_id' => Company::factory(),
            'type'=>$this->faker->boolean(),
        ];
    }
}
