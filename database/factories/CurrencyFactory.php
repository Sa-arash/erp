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
    protected static bool $hasCompanyCurrency = false;

     public function definition(): array
     {
         $currencies = [
             ['name' => 'USD', 'symbol' => '$'],
             ['name' => 'IRR', 'symbol' => '﷼'],
             ['name' => 'EUR', 'symbol' => '€'],
             ['name' => 'GBP', 'symbol' => '£'],
             ['name' => 'JPY', 'symbol' => '¥']
         ];
         
         $selectedCurrency = $this->faker->randomElement($currencies);
         
         $isCompanyCurrency = !self::$hasCompanyCurrency;
         self::$hasCompanyCurrency = true;
         
         return [
             'name' => $selectedCurrency['name'],
             'symbol' => $selectedCurrency['symbol'],
             'company_id' => 1,
             'exchange_rate' => 1,
             'is_company_currency' => ($isCompanyCurrency ? 1 : 0),
         ];
     }
}
