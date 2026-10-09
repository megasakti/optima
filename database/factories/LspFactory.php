<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Lsp;
use Illuminate\Database\Eloquent\Factories\Factory;

class LspFactory extends Factory
{
    protected $model = Lsp::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => strtoupper(fake()->unique()->lexify('LSP-????')),
            'name' => 'LSP ' . fake()->words(2, true),
            'bnsp_license_number' => 'BNSP-LSP-' . fake()->numerify('###') . '-ID/2023',
            'pic_name' => fake()->name(),
            'pic_phone' => fake()->phoneNumber(),
            'pic_email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }
}
