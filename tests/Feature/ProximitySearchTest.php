<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProximitySearchTest extends TestCase
{
    use RefreshDatabase;

    private const ORIGIN = ['lat' => -22.8962, 'lng' => -47.0521];

    protected function setUp(): void
    {
        parent::setUp();

        AutomotiveDetailing::factory()->create(['name' => 'Estética Vizinha', 'neighborhood' => 'Cambuí', 'latitude' => -22.8987, 'longitude' => -47.0489]);
        AutomotiveDetailing::factory()->create(['name' => 'Estética Barão', 'neighborhood' => 'Barão Geraldo', 'latitude' => -22.8193, 'longitude' => -47.0802]);
        AutomotiveDetailing::factory()->create(['name' => 'Estética Capital', 'neighborhood' => 'Sé', 'latitude' => -23.5505, 'longitude' => -46.6333]);
        AutomotiveDetailing::factory()->create(['name' => 'Estética Sem Mapa', 'neighborhood' => 'Centro', 'latitude' => null, 'longitude' => null]);
    }

    public function test_orders_by_distance_when_location_is_given(): void
    {
        $this->get(route('detailings.index', self::ORIGIN))
            ->assertOk()
            ->assertSeeInOrder(['Estética Vizinha', 'Estética Barão', 'Estética Capital', 'Estética Sem Mapa']);
    }

    public function test_computes_distance_with_haversine(): void
    {
        $detailings = $this->get(route('detailings.index', self::ORIGIN))->viewData('detailings');

        $distances = $detailings->pluck('distance_km', 'name')->map(fn ($value) => $value === null ? null : round((float) $value, 2));

        $this->assertEqualsWithDelta(0.43, $distances['Estética Vizinha'], 0.05);
        $this->assertEqualsWithDelta(9.0, $distances['Estética Barão'], 0.1);
        $this->assertEqualsWithDelta(84.4, $distances['Estética Capital'], 0.1);
        $this->assertNull($distances['Estética Sem Mapa']);
    }

    public function test_card_shows_distance(): void
    {
        $this->get(route('detailings.index', self::ORIGIN))
            ->assertSee('A 430 m de você')
            ->assertSee('A 9,0 km de você');
    }

    public function test_filters_by_radius(): void
    {
        $this->get(route('detailings.index', [...self::ORIGIN, 'raio' => 10]))
            ->assertSee('Estética Vizinha')
            ->assertSee('Estética Barão')
            ->assertDontSee('Estética Capital')
            ->assertDontSee('Estética Sem Mapa');

        $this->get(route('detailings.index', [...self::ORIGIN, 'raio' => 2]))
            ->assertSee('Estética Vizinha')
            ->assertDontSee('Estética Barão');
    }

    public function test_combines_location_with_other_filters_and_sort(): void
    {
        $this->get(route('detailings.index', [...self::ORIGIN, 'raio' => 10, 'q' => 'barão', 'ordem' => 'nome']))
            ->assertSee('Estética Barão')
            ->assertDontSee('Estética Vizinha');
    }

    public function test_distance_sort_without_location_falls_back_to_name(): void
    {
        $this->get(route('detailings.index', ['ordem' => 'distancia']))
            ->assertOk()
            ->assertSeeInOrder(['Estética Barão', 'Estética Capital', 'Estética Sem Mapa', 'Estética Vizinha'])
            ->assertDontSee('de você');
    }

    public function test_catalog_works_without_location(): void
    {
        $this->get(route('detailings.index'))
            ->assertOk()
            ->assertSee('Usar minha localização')
            ->assertDontSee('de você');
    }

    public static function invalidCoordinates(): array
    {
        return [
            'latitude acima de 90' => [['lat' => 90.1, 'lng' => 0], 'lat'],
            'latitude abaixo de -90' => [['lat' => -91, 'lng' => 0], 'lat'],
            'longitude acima de 180' => [['lat' => 0, 'lng' => 181], 'lng'],
            'longitude abaixo de -180' => [['lat' => 0, 'lng' => -180.5], 'lng'],
            'latitude textual' => [['lat' => '1) or (1=1', 'lng' => 0], 'lat'],
            'longitude sem latitude' => [['lng' => -47], 'lat'],
            'raio sem localização' => [['raio' => 5], 'lat'],
            'raio fora da faixa' => [['lat' => 0, 'lng' => 0, 'raio' => 5000], 'raio'],
            'raio negativo' => [['lat' => 0, 'lng' => 0, 'raio' => -5], 'raio'],
        ];
    }

    #[DataProvider('invalidCoordinates')]
    public function test_invalid_location_input_is_rejected(array $query, string $field): void
    {
        $this->get(route('detailings.index', $query))
            ->assertRedirect(route('detailings.index'))
            ->assertSessionHasErrors($field);
    }

    public function test_owner_can_inform_coordinates(): void
    {
        $detailing = AutomotiveDetailing::factory()->for(User::factory(), 'owner')->create();

        $this->actingAs($detailing->owner)
            ->put(route('owner.detailing.update'), [
                ...$detailing->only(['name', 'whatsapp', 'postal_code', 'street', 'number', 'neighborhood', 'city', 'state']),
                'latitude' => '-22.9000000',
                'longitude' => '-47.0600000',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('-22.9000000', $detailing->fresh()->latitude);

        $this->actingAs($detailing->owner)
            ->put(route('owner.detailing.update'), [
                ...$detailing->only(['name', 'whatsapp', 'postal_code', 'street', 'number', 'neighborhood', 'city', 'state']),
                'latitude' => '-95',
                'longitude' => null,
            ])
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }
}
