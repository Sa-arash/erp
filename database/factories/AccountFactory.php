<?php

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public $name ; 
    public function definition(): array
    {

        
        return [
            'holder_name' => $this->faker->name,
            'bank_name' => $this->faker->company,
            'account_number' => $this->faker->bankAccountNumber,
            'initial_balance' => $this->faker->randomFloat(2, 1000, 10000),
            'contact_number' => $this->faker->phoneNumber,
            'branch' => $this->faker->word,
            'address' => $this->faker->address,
            'account_type_id'=>AccountType::factory(),
            'company_id' => Company::factory(),
            'amount'=>$this->faker->randomNumber(),
            'swift_code' => $this->faker->swiftBicNumber,
            'description' => $this->faker->paragraph,
            'currency' => $this->faker->currencyCode(),

        ];
    }
}
