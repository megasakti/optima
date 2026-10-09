<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => strtoupper(fake()->unique()->lexify('CUST-????')),
            'name' => fake()->company(),
            'customer_type' => fake()->randomElement(['corporate', 'government', 'individual', 'institution']),
            'pic_name' => fake()->name(),
            'pic_phone' => fake()->phoneNumber(),
            'pic_email' => fake()->email(),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'address' => fake()->address(),
            'payment_term_days' => 30,
            'credit_limit' => fake()->numberBetween(10000000, 200000000),
            'is_active' => true,
        ];
    }
}
