<?php

namespace Database\Factories;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AutomotiveDetailing>
 */
class AutomotiveDetailingFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' Estética Automotiva';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'phone' => fake()->numerify('(19) 3###-####'),
            'whatsapp' => fake()->numerify('(19) 9####-####'),
            'email' => fake()->unique()->safeEmail(),
            'postal_code' => fake()->numerify('130##-###'),
            'street' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'complement' => null,
            'neighborhood' => fake()->randomElement(['Cambuí', 'Taquaral', 'Centro', 'Barão Geraldo', 'Castelo']),
            'city' => 'Campinas',
            'state' => 'SP',
            'latitude' => fake()->latitude(-22.95, -22.85),
            'longitude' => fake()->longitude(-47.12, -47.00),
            'status' => DetailingStatus::Approved,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => DetailingStatus::Pending]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => DetailingStatus::Rejected]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => DetailingStatus::Inactive]);
    }
}
