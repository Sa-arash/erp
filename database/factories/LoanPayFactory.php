<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanPay>
 */
class LoanPayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'payment_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'amount_pay' => $this->faker->numberBetween(1000, 10000),
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
