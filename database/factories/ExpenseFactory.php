<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Bank_category;
use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'reference' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'payment_receipt_image' => $this->faker->imageUrl(),
            'company_id' => Company::factory(),
            'vendor_id' => Vendor::factory(),
            'category_id' => Bank_category::factory(),
        ];
    }
}
