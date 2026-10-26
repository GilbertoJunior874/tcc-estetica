<?php

namespace Tests\Feature;

use App\Models\AutomotiveDetailing;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappContactTest extends TestCase
{
    use RefreshDatabase;

    private AutomotiveDetailing $detailing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detailing = AutomotiveDetailing::factory()->create(['whatsapp' => '(19) 99876-5432']);
    }

    private function service(array $attributes, ?AutomotiveDetailing $detailing = null): Service
    {
        return Service::factory()->for($detailing ?? $this->detailing)->create($attributes);
    }

    private function messageFrom(string $url): string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return $query['text'];
    }

    public function test_detail_page_lets_visitor_select_active_services(): void
    {
        $active = $this->service(['name' => 'Lavagem detalhada', 'price_cents' => 12000, 'duration_minutes' => 90]);
        $this->service(['name' => 'Serviço pausado', 'active' => false]);

        $this->get(route('detailings.show', $this->detailing))
            ->assertOk()
            ->assertSee('name="servicos[]" value="'.$active->id.'"', false)
            ->assertSee('data-price="12000"', false)
            ->assertSee('1h30')
            ->assertSee('Total estimado')
            ->assertDontSee('Serviço pausado');
    }

    public function test_redirects_to_whatsapp_with_selected_services_and_total(): void
    {
        $wash = $this->service(['name' => 'Lavagem simples', 'price_cents' => 4990]);
        $polish = $this->service(['name' => 'Polimento técnico', 'price_cents' => 85000]);

        $response = $this->get(route('detailings.whatsapp', [$this->detailing, 'servicos' => [$polish->id, $wash->id]]));

        $url = $response->assertRedirect()->headers->get('Location');

        $this->assertStringStartsWith('https://wa.me/5519998765432?text=', $url);
        $this->assertSame(implode("\n", [
            'Olá! Encontrei vocês pelo '.config('app.name').' e gostaria de saber mais sobre os seguintes serviços:',
            '',
            '- Lavagem simples: R$ 49,90',
            '- Polimento técnico: R$ 850,00',
            '',
            'Total estimado: R$ 899,90',
            '',
            'Podem confirmar valores e disponibilidade?',
        ]), $this->messageFrom($url));
    }

    public function test_message_is_url_encoded(): void
    {
        $service = $this->service(['name' => 'Lavagem & cera #1 ?', 'price_cents' => 1000]);

        $url = $this->get(route('detailings.whatsapp', [$this->detailing, 'servicos' => [$service->id]]))->headers->get('Location');

        $this->assertStringNotContainsString(' ', $url);
        $this->assertStringNotContainsString("\n", $url);
        $this->assertStringContainsString('Lavagem%20%26%20cera%20%231%20%3F', $url);
        $this->assertStringContainsString('- Lavagem & cera #1 ?: R$ 10,00', $this->messageFrom($url));
    }

    public function test_total_is_summed_in_cents(): void
    {
        $services = new Collection([
            $this->service(['name' => 'A', 'price_cents' => 1]),
            $this->service(['name' => 'B', 'price_cents' => 2]),
            $this->service(['name' => 'C', 'price_cents' => 99997]),
        ]);

        $this->assertStringContainsString('Total estimado: R$ 1.000,00', $this->detailing->whatsappMessage($services));
    }

    public function test_ignores_services_from_other_detailings_and_inactive_services(): void
    {
        $own = $this->service(['name' => 'Lavagem própria', 'price_cents' => 5000]);
        $inactive = $this->service(['name' => 'Serviço inativo', 'price_cents' => 7000, 'active' => false]);
        $foreign = $this->service(['name' => 'Serviço de outra estética', 'price_cents' => 9000], AutomotiveDetailing::factory()->create());

        $message = $this->messageFrom(
            $this->get(route('detailings.whatsapp', [$this->detailing, 'servicos' => [$own->id, $inactive->id, $foreign->id]]))->headers->get('Location'),
        );

        $this->assertStringContainsString('Lavagem própria', $message);
        $this->assertStringNotContainsString('Serviço inativo', $message);
        $this->assertStringNotContainsString('Serviço de outra estética', $message);
        $this->assertStringContainsString('Total estimado: R$ 50,00', $message);
    }

    public function test_without_selection_sends_generic_message(): void
    {
        $url = $this->get(route('detailings.whatsapp', $this->detailing))->headers->get('Location');

        $this->assertSame(
            'Olá! Encontrei vocês pelo '.config('app.name').' e gostaria de mais informações sobre os serviços.',
            $this->messageFrom($url),
        );
    }

    public function test_rejects_malformed_service_selection(): void
    {
        $this->get(route('detailings.whatsapp', [$this->detailing, 'servicos' => ['1 or 1=1']]))
            ->assertSessionHasErrors('servicos.0');

        $this->get(route('detailings.whatsapp', [$this->detailing, 'servicos' => range(1, 51)]))
            ->assertSessionHasErrors('servicos');
    }

    public function test_unapproved_detailing_has_no_whatsapp_contact(): void
    {
        foreach (['pending', 'rejected', 'inactive'] as $state) {
            $detailing = AutomotiveDetailing::factory()->{$state}()->create();

            $this->get(route('detailings.whatsapp', $detailing))->assertNotFound();
        }
    }

    public function test_phone_number_is_sanitized(): void
    {
        $cases = [
            '(19) 99876-5432' => '5519998765432',
            '19 3234-5678' => '551932345678',
            '+55 (11) 91234-5678' => '5511912345678',
            '123' => null,
            '' => null,
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, (new AutomotiveDetailing(['whatsapp' => $input]))->whatsapp_number, "Entrada: {$input}");
        }
    }

    public function test_invalid_whatsapp_number_hides_contact(): void
    {
        $this->detailing->forceFill(['whatsapp' => '123'])->save();

        $this->get(route('detailings.whatsapp', $this->detailing))->assertNotFound();
        $this->get(route('detailings.show', $this->detailing))->assertOk()->assertDontSee('Conversar no WhatsApp');
    }
}
