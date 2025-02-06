<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['USD','IR']),
            'symbol' => $this->faker->randomElement(['$','﷼']),
            'company_id' => 1,
            'exchange_rate'=>1,
            'is_company_currency'=>1
        ];
    }
}
