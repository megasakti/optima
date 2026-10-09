<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => fake()->unique()->numerify('###'),
            'name' => strtoupper(fake()->words(2, true)),
            'type' => fake()->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'normal_balance' => fake()->randomElement(['debit', 'credit']),
            'category' => 'operating_expense',
            'is_cash_bank' => false,
            'is_active' => true,
        ];
    }
}
