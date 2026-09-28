<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_shows_detailing_details_and_active_services(): void
    {
        $detailing = AutomotiveDetailing::factory()->create([
            'street' => 'Rua das Flores',
            'number' => '120',
            'whatsapp' => '(19) 99876-5432',
        ]);
        Service::factory()->for($detailing)->create([
            'name' => 'Polimento técnico',
            'description' => 'Correção de pintura em etapas.',
            'price_cents' => 85000,
            'duration_minutes' => 480,
        ]);
        Service::factory()->for($detailing)->inactive()->create(['name' => 'Serviço desativado']);

        $this->get(route('detailings.show', $detailing))
            ->assertOk()
            ->assertSee($detailing->name)
            ->assertSee('Rua das Flores, 120')
            ->assertSee('Polimento técnico')
            ->assertSee('Correção de pintura em etapas.')
            ->assertSee('850,00')
            ->assertSee('8h')
            ->assertSee('https://wa.me/5519998765432')
            ->assertDontSee('Serviço desativado');
    }

    public function test_unapproved_detailing_is_not_found(): void
    {
        $detailing = AutomotiveDetailing::factory()->pending()->create();

        $this->get(route('detailings.show', $detailing))->assertNotFound();
    }

    public function test_unknown_slug_is_not_found(): void
    {
        $this->get('/esteticas/nao-existe')->assertNotFound();
    }
}
