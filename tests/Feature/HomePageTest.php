<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_approved_detailings(): void
    {
        $approved = AutomotiveDetailing::factory()->has(Service::factory()->state(['price_cents' => 6000]))->create();
        $pending = AutomotiveDetailing::factory()->pending()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Encontre uma estética automotiva perto de você')
            ->assertSee($approved->name)
            ->assertSee('60,00')
            ->assertDontSee($pending->name);
    }
}
