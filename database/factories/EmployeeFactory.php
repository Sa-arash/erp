<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory 
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fullName' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'birthday' => $this->faker->dateTime(),
            'joining_date' => $this->faker->dateTime(),
            'leave_date' => $this->faker->datetime(),
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
            'duty_id' => Duty::factory(),
            'cart' => $this->faker->creditCardNumber,
            'bank' => $this->faker->company,
            'tin' => $this->faker->optional()->numerify('##########'),
            'base_salary' => $this->faker->numberBetween(30000, 100000),
            'benefit_salary' => $this->faker->numberBetween(0, 20000),
            'ID_number' => $this->faker->numberBetween(0, 9999),
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'type_of_ID' => $this->faker->randomElement(['New','Renewal','Mutilated','Loss','Theft']),
            'gender' => $this->faker->randomElement(['male','female','other']),
            'count_of_child' => $this->faker->numberBetween(0, 5),
            'emergency_phone_number' => $this->faker->optional()->phoneNumber,
            'pic' => $this->faker->optional()->imageUrl(100, 100, 'people'),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'company_id' => Company::factory(),
            'NIC'=>$this->faker->phoneNumber,
            'contract_id'=>Contract::factory(),
        ];
    }
}
