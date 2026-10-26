<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\BusinessHour;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogSearchTest extends TestCase
{
    use RefreshDatabase;

    private function detailing(string $name, array $attributes = [], array $services = []): AutomotiveDetailing
    {
        $detailing = AutomotiveDetailing::factory()->create(['name' => $name, ...$attributes]);

        foreach ($services as $serviceName => $price) {
            Service::factory()->for($detailing)->create(['name' => $serviceName, 'price_cents' => $price]);
        }

        return $detailing;
    }

    public function test_search_matches_name_city_neighborhood_and_service(): void
    {
        $this->detailing('Brilho Norte', ['neighborhood' => 'Cambuí', 'city' => 'Campinas']);
        $this->detailing('Espelho Sul', ['neighborhood' => 'Taquaral', 'city' => 'Campinas'], ['Polimento técnico' => 60000]);
        $this->detailing('Lava Rápido Hortolândia', ['neighborhood' => 'Centro', 'city' => 'Hortolândia']);

        $this->get(route('detailings.index', ['q' => 'brilho']))
            ->assertSee('Brilho Norte')->assertDontSee('Espelho Sul');

        $this->get(route('detailings.index', ['q' => 'taquaral']))
            ->assertSee('Espelho Sul')->assertDontSee('Brilho Norte');

        $this->get(route('detailings.index', ['q' => 'hortolândia']))
            ->assertSee('Lava Rápido Hortolândia')->assertDontSee('Brilho Norte');

        $this->get(route('detailings.index', ['q' => 'polimento']))
            ->assertSee('Espelho Sul')->assertDontSee('Brilho Norte')->assertDontSee('Lava Rápido Hortolândia');
    }

    public function test_search_ignores_inactive_services(): void
    {
        $detailing = $this->detailing('Estética Antiga');
        Service::factory()->for($detailing)->inactive()->create(['name' => 'Vitrificação']);

        $this->get(route('detailings.index', ['q' => 'vitrificação']))
            ->assertOk()
            ->assertDontSee('Estética Antiga');

        $this->get(route('detailings.index', ['servico' => 'vitrificação']))
            ->assertDontSee('Estética Antiga');
    }

    public function test_search_only_returns_approved_detailings(): void
    {
        AutomotiveDetailing::factory()->pending()->create(['name' => 'Polimento Pendente']);
        AutomotiveDetailing::factory()->inactive()->create(['name' => 'Polimento Inativo']);
        AutomotiveDetailing::factory()->rejected()->create(['name' => 'Polimento Recusado']);
        $this->detailing('Polimento Aprovado');

        $this->get(route('detailings.index', ['q' => 'polimento']))
            ->assertSee('Polimento Aprovado')
            ->assertDontSee('Polimento Pendente')
            ->assertDontSee('Polimento Inativo')
            ->assertDontSee('Polimento Recusado');
    }

    public function test_like_wildcards_are_treated_as_text(): void
    {
        $this->detailing('Estética Comum');

        $this->get(route('detailings.index', ['q' => '%']))
            ->assertOk()
            ->assertDontSee('Estética Comum');

        $this->get(route('detailings.index', ['q' => "' or 1=1 --"]))
            ->assertOk()
            ->assertDontSee('Estética Comum');
    }

    public function test_filters_by_city(): void
    {
        $this->detailing('Estética Campinas', ['city' => 'Campinas']);
        $this->detailing('Estética Valinhos', ['city' => 'Valinhos']);

        $this->get(route('detailings.index', ['cidade' => 'Valinhos']))
            ->assertSee('Estética Valinhos')
            ->assertDontSee('Estética Campinas');
    }

    public function test_city_options_only_list_approved_detailings(): void
    {
        $this->detailing('Estética Campinas', ['city' => 'Campinas']);
        AutomotiveDetailing::factory()->pending()->create(['city' => 'Cidade Oculta']);

        $this->get(route('detailings.index'))
            ->assertSee('<option value="Campinas"', false)
            ->assertDontSee('Cidade Oculta');
    }

    public function test_filters_by_service_and_price_range_in_cents(): void
    {
        $this->detailing('Estética Barata', [], ['Lavagem simples' => 4990]);
        $this->detailing('Estética Média', [], ['Lavagem detalhada' => 12000]);
        $this->detailing('Estética Cara', [], ['Lavagem premium' => 30001]);

        $this->get(route('detailings.index', ['servico' => 'lavagem', 'preco_min' => 50, 'preco_max' => 300]))
            ->assertSee('Estética Média')
            ->assertDontSee('Estética Barata')
            ->assertDontSee('Estética Cara');

        $this->get(route('detailings.index', ['preco_max' => 49]))
            ->assertDontSee('Estética Barata');

        $this->get(route('detailings.index', ['preco_max' => 50]))
            ->assertSee('Estética Barata');
    }

    public function test_service_and_price_must_match_the_same_service(): void
    {
        $this->detailing('Estética Mista', [], ['Polimento técnico' => 80000, 'Lavagem simples' => 5000]);

        $this->get(route('detailings.index', ['servico' => 'polimento', 'preco_max' => 100]))
            ->assertDontSee('Estética Mista');

        $this->get(route('detailings.index', ['servico' => 'polimento', 'preco_max' => 900]))
            ->assertSee('Estética Mista');
    }

    public function test_combines_search_city_service_and_price(): void
    {
        $this->detailing('Brilho Campinas', ['city' => 'Campinas', 'neighborhood' => 'Cambuí'], ['Enceramento' => 15000]);
        $this->detailing('Brilho Valinhos', ['city' => 'Valinhos', 'neighborhood' => 'Centro'], ['Enceramento' => 15000]);
        $this->detailing('Outro Campinas', ['city' => 'Campinas', 'neighborhood' => 'Cambuí'], ['Enceramento' => 15000]);

        $this->get(route('detailings.index', ['q' => 'brilho', 'cidade' => 'Campinas', 'servico' => 'encera', 'preco_min' => 100, 'preco_max' => 200]))
            ->assertSee('Brilho Campinas')
            ->assertDontSee('Brilho Valinhos')
            ->assertDontSee('Outro Campinas');
    }

    public function test_pagination_preserves_filters(): void
    {
        AutomotiveDetailing::factory()->count(13)->sequence(fn ($sequence) => ['name' => sprintf('Polimento %02d', $sequence->index)])->create();

        $this->get(route('detailings.index', ['q' => 'polimento', 'cidade' => 'Campinas']))
            ->assertSee('q=polimento', false)
            ->assertSee('cidade=Campinas', false)
            ->assertSee('page=2', false);

        $this->get(route('detailings.index', ['q' => 'polimento', 'page' => 2]))
            ->assertOk()
            ->assertSee('Polimento 12');
    }

    public function test_sorts_by_lowest_price(): void
    {
        $this->detailing('A Cara', [], ['Lavagem' => 9000]);
        $this->detailing('B Barata', [], ['Lavagem' => 3000]);

        $this->get(route('detailings.index', ['ordem' => 'preco']))
            ->assertSeeInOrder(['B Barata', 'A Cara']);
    }

    public static function invalidFilters(): array
    {
        return [
            'preço negativo' => [['preco_min' => -1], 'preco_min'],
            'preço não numérico' => [['preco_max' => 'abc'], 'preco_max'],
            'máximo menor que mínimo' => [['preco_min' => 200, 'preco_max' => 100], 'preco_max'],
            'busca longa' => [['q' => str_repeat('a', 101)], 'q'],
            'ordenação desconhecida' => [['ordem' => 'id desc'], 'ordem'],
        ];
    }

    #[DataProvider('invalidFilters')]
    public function test_invalid_filters_are_rejected(array $query, string $field): void
    {
        $this->get(route('detailings.index', $query))
            ->assertRedirect(route('detailings.index'))
            ->assertSessionHasErrors($field);
    }

    public function test_card_shows_whether_detailing_is_open_now(): void
    {
        $open = $this->detailing('Estética Aberta');
        $closed = $this->detailing('Estética Fechada');

        foreach ([$open, $closed] as $detailing) {
            BusinessHour::factory()->for($detailing)->create([
                'day_of_week' => 1,
                'opens_at' => $detailing->is($open) ? '08:00' : '13:00',
                'closes_at' => '18:00',
            ]);
        }

        $this->travelTo(Carbon::parse('2026-10-19 10:30'));

        $this->get(route('detailings.index'))
            ->assertSeeInOrder(['Estética Aberta', 'Aberta agora', 'Estética Fechada', 'Fechada agora']);
    }

    public function test_card_omits_open_state_without_business_hours(): void
    {
        $this->detailing('Estética Sem Horário');

        $this->get(route('detailings.index'))
            ->assertDontSee('Aberta agora')
            ->assertDontSee('Fechada agora');
    }
}
