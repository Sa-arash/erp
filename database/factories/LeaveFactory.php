<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Typeleave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leave>
 */
class LeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startLeave = $this->faker->dateTimeBetween('-1 year', 'now');
        $days = $this->faker->numberBetween(1, 30);

        return [
            'employee_id' => Employee::factory(),
            'typeleave_id' => Typeleave::factory(),
            'start_leave' => $startLeave,
            'end_leave' => $this->faker->optional()->dateTimeBetween($startLeave, '+1 month'),
            'days' => $days,
            'document' => $this->faker->optional()->text(200),
            'description' => $this->faker->optional()->paragraph,
            'status' => $this->faker->randomElement(['waiting', 'rejected', 'accepted', 'progressed', 'finished']),
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
        ];
        
    }
}
