<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'customer_id' => null,
            'company_name' => fake()->company(),
            'category' => fake()->randomElement(['public', 'iht', 'webinar']),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'training_title' => 'Sertifikasi Ahli K3 Umum BNSP',
            'training_program_id' => null,
            'training_batch_id' => null,
            'training_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'location' => fake()->city(),
            'price' => fake()->randomElement([150000, 3500000, 4500000, 5500000]),
            'source' => fake()->randomElement(['web', 'ig', 'bod']),
            'lsp_id' => null,
            'payment_status' => fake()->randomElement(['unpaid', 'partial', 'paid']),
            'certificate_status' => fake()->randomElement(['process', 'passed', 'issued']),
            'notes' => fake()->sentence(),
        ];
    }
}
