<?php

namespace Database\Factories;

use App\Models\AutomotiveDetailing;
use App\Models\BusinessHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessHour>
 */
class BusinessHourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'automotive_detailing_id' => AutomotiveDetailing::factory(),
            'day_of_week' => 1,
            'opens_at' => '08:00',
            'closes_at' => '18:00',
            'closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(['opens_at' => null, 'closes_at' => null, 'closed' => true]);
    }
}
