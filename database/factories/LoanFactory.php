<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requestDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $amount = $this->faker->numberBetween(5000, 50000);
        $numberOfInstallments = $this->faker->numberBetween(1, 24);

        return [
            'employee_id' => Employee::factory(),
            'loan_code' => $this->faker->unique()->randomNumber(6),
            'request_amount' => $amount,
            'amount' => $amount,
            'number_of_installments' => $numberOfInstallments,
            'number_of_payed_installments' => $this->faker->numberBetween(0, $numberOfInstallments),
            'request_date' => $requestDate,
            'answer_date' => $this->faker->optional()->dateTimeBetween($requestDate, 'now'),
            'status' => $this->faker->randomElement(['waiting','progressed','rejected','accepted','finished']),
            'company_id' => Company::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
