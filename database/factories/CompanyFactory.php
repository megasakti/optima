<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => strtoupper(fake()->unique()->lexify('CMP-????')),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'currency' => 'IDR',
            'is_active' => true,
        ];
    }
}
