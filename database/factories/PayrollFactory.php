<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payroll>
 */
class PayrollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'employee_id' => Employee::factory(),
            'amount_pay' => $this->faker->numberBetween(10000, 100000),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['pending','payed','rejected']),
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
