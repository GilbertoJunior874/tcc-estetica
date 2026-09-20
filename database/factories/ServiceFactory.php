<?php

namespace Database\Factories;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'automotive_detailing_id' => AutomotiveDetailing::factory(),
            'name' => fake()->randomElement(['Lavagem simples', 'Lavagem detalhada', 'Higienização interna', 'Polimento técnico', 'Vitrificação de pintura']),
            'description' => fake()->sentence(12),
            'price_cents' => fake()->numberBetween(40, 1500) * 100,
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120, 240, 480]),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
