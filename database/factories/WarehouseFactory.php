<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'title' => $this->faker->unique()->randomElement([
                'Main Warehouse',
                'North Warehouse',
                'South Warehouse',
                'East Warehouse',
                'West Warehouse',
                'Central Warehouse',
                'Distribution Center',
                'Storage Facility',
                'Logistics Hub'
            ]),
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->unique()->randomElement(getCountry()),
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
            // 'employee_id' => Employee::factory(),
            'company_id' => 1,

        ];
    }
}
