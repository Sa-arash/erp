<?php

namespace Database\Factories;

use App\Models\Benefit;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BenefitEmployee>
 */
class BenefitEmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
         'employee_id' => Employee::factory(),
            'benefit_id' => Benefit::factory(),
            'company_id' => Company::factory(),
        ];
    }
}
