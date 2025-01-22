<?php

namespace Database\Factories;

use App\Models\Bank_category;
use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'bill_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'category_id' => Bank_category::factory(),
            'order_number' => $this->faker->word,
            'recurring_bill' => $this->faker->randomElement(['daily', 'monthly', 'weekly', 'none']),
            'company_id' => Company::factory(),
            'description' => $this->faker->paragraph,
        ];
    }
}
