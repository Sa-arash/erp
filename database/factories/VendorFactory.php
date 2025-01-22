<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\VendorType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'company_id' => Company::factory(),
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'description' => $this->faker->paragraph,
            'vendor_type_id'=>VendorType::factory(),
        ];
    }
}
