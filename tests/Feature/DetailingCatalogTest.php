<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailingCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_only_approved_detailings(): void
    {
        $approved = AutomotiveDetailing::factory()->create(['neighborhood' => 'Cambuí']);
        $pending = AutomotiveDetailing::factory()->pending()->create();
        $rejected = AutomotiveDetailing::factory()->rejected()->create();
        $inactive = AutomotiveDetailing::factory()->inactive()->create();

        $this->get(route('detailings.index'))
            ->assertOk()
            ->assertSee($approved->name)
            ->assertSee('Cambuí, Campinas – SP')
            ->assertDontSee($pending->name)
            ->assertDontSee($rejected->name)
            ->assertDontSee($inactive->name);
    }

    public function test_catalog_shows_starting_price_from_active_services(): void
    {
        $detailing = AutomotiveDetailing::factory()->create();
        Service::factory()->for($detailing)->create(['price_cents' => 15000]);
        Service::factory()->for($detailing)->create(['price_cents' => 7990]);
        Service::factory()->for($detailing)->inactive()->create(['price_cents' => 1000]);

        $this->get(route('detailings.index'))
            ->assertOk()
            ->assertSee('A partir de')
            ->assertSee('79,90')
            ->assertDontSee('10,00');
    }

    public function test_catalog_handles_empty_state(): void
    {
        $this->get(route('detailings.index'))
            ->assertOk()
            ->assertSee('Ainda não há estéticas disponíveis.');
    }
}
