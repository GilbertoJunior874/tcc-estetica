<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DetailingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'cnpj' => '00000000000191',
            'name' => 'Lustro Fino Estética',
            'description' => 'Polimento e higienização com hora marcada.',
            'phone' => '(19) 3233-4455',
            'whatsapp' => '(19) 98877-6655',
            'email' => 'contato@lustrofino.test',
            'postal_code' => '13025-320',
            'street' => 'Rua Coronel Quirino',
            'number' => '410',
            'complement' => null,
            'neighborhood' => 'Cambuí',
            'city' => 'Campinas',
            'state' => 'sp',
        ], $overrides);
    }

    public function test_owner_sees_registration_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('owner.detailing.create'))
            ->assertOk()
            ->assertSee('Enviar para análise');
    }

    public function test_owner_can_submit_registration_request_as_pending(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('owner.detailing.store'), $this->payload())
            ->assertRedirect(route('owner.dashboard'));

        $detailing = $user->automotiveDetailing()->first();

        $this->assertSame('Lustro Fino Estética', $detailing->name);
        $this->assertSame('lustro-fino-estetica', $detailing->slug);
        $this->assertSame('SP', $detailing->state);
        $this->assertSame(DetailingStatus::Pending, $detailing->status);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertSee(DetailingStatus::Pending->label());
    }

    public function test_registration_validates_required_fields(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('owner.detailing.store'), $this->payload([
                'name' => '',
                'whatsapp' => '123',
                'postal_code' => 'abc',
                'state' => 'XX',
                'email' => 'invalido',
            ]))
            ->assertSessionHasErrors(['name', 'whatsapp', 'postal_code', 'state', 'email']);

        $this->assertDatabaseCount('automotive_detailings', 0);
    }

    public function test_owner_cannot_choose_status_or_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post(route('owner.detailing.store'), $this->payload([
            'status' => DetailingStatus::Approved->value,
            'user_id' => $other->id,
            'slug' => 'slug-escolhido',
        ]));

        $detailing = AutomotiveDetailing::sole();

        $this->assertSame(DetailingStatus::Pending, $detailing->status);
        $this->assertTrue($detailing->owner->is($user));
        $this->assertNotSame('slug-escolhido', $detailing->slug);
    }

    public function test_owner_cannot_register_a_second_detailing(): void
    {
        $user = User::factory()->create();
        AutomotiveDetailing::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->post(route('owner.detailing.store'), $this->payload())
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('owner.detailing.create'))
            ->assertRedirect(route('owner.dashboard'));

        $this->assertDatabaseCount('automotive_detailings', 1);
    }

    public function test_slug_is_unique_when_names_repeat(): void
    {
        AutomotiveDetailing::factory()->create(['name' => 'Lustro Fino Estética', 'slug' => 'lustro-fino-estetica']);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('owner.detailing.store'), $this->payload());

        $this->assertSame('lustro-fino-estetica-2', $user->automotiveDetailing->slug);
    }

    public function test_pending_request_is_not_public(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('owner.detailing.store'), $this->payload());

        $this->get(route('detailings.index'))->assertDontSee('Lustro Fino Estética');
        $this->get(route('detailings.show', $user->automotiveDetailing))->assertNotFound();
    }

    #[DataProvider('validRegistrationData')]
    public function test_registration_normalizes_valid_postal_code_and_cnpj(string $postalCode, string $cnpj): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('owner.detailing.store'), $this->payload(['postal_code' => $postalCode, 'cnpj' => $cnpj]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('automotive_detailings', ['postal_code' => '85010000', 'cnpj' => '00000000000191']);
        $this->assertSame('85010-000', AutomotiveDetailing::sole()->formatted_postal_code);
        $this->assertSame('00.000.000/0001-91', AutomotiveDetailing::sole()->formatted_cnpj);
    }

    public static function validRegistrationData(): array
    {
        return [
            ['85010-000', '00.000.000/0001-91'],
            ['85010000', '00000000000191'],
        ];
    }

    #[DataProvider('invalidRegistrationData')]
    public function test_registration_rejects_invalid_postal_code_and_cnpj(string $field, mixed $value): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('owner.detailing.store'), $this->payload([$field => $value]))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('automotive_detailings', 0);
    }

    public static function invalidRegistrationData(): array
    {
        return [
            'CEP obrigatório' => ['postal_code', ''],
            'CEP incompleto' => ['postal_code', '85010-00'],
            'CEP longo' => ['postal_code', '850100000'],
            'CEP com letras' => ['postal_code', 'abc85010000'],
            'CEP com símbolo inválido' => ['postal_code', '85010/000'],
            'CEP em array' => ['postal_code', ['85010000']],
            'CNPJ obrigatório' => ['cnpj', ''],
            'CNPJ incompleto' => ['cnpj', '0000000000019'],
            'CNPJ longo' => ['cnpj', '000000000001910'],
            'CNPJ com letras' => ['cnpj', 'abc00000000000191'],
            'CNPJ com símbolo inválido' => ['cnpj', '00.000.000/0001+91'],
            'CNPJ com dígitos iguais' => ['cnpj', '11111111111111'],
            'CNPJ zerado' => ['cnpj', '00000000000000'],
            'Primeiro verificador incorreto' => ['cnpj', '00000000000181'],
            'Segundo verificador incorreto' => ['cnpj', '00.000.000/0001-90'],
            'CNPJ em array' => ['cnpj', ['00000000000191']],
        ];
    }

    public function test_registration_rejects_duplicate_cnpj(): void
    {
        AutomotiveDetailing::factory()->create(['cnpj' => '00000000000191']);

        $this->actingAs(User::factory()->create())
            ->post(route('owner.detailing.store'), $this->payload(['cnpj' => '00.000.000/0001-91']))
            ->assertSessionHasErrors(['cnpj' => 'Este CNPJ já está cadastrado em outra estética.']);

        $this->assertDatabaseCount('automotive_detailings', 1);
    }

    public function test_registration_form_identifies_required_and_optional_fields(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('owner.detailing.create'))
            ->assertOk()
            ->assertSee('Campos marcados com * são obrigatórios.');

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new \DOMXPath($document);

        foreach (['name', 'cnpj', 'whatsapp', 'postal_code', 'street', 'number', 'neighborhood', 'city', 'state'] as $field) {
            $this->assertSame(1, $xpath->query("//*[@id='$field'][@required]")->length);
            $this->assertStringContainsString('*', $xpath->query("//label[@for='$field']")->item(0)->textContent);
        }

        foreach (['description', 'phone', 'email', 'complement', 'latitude', 'longitude'] as $field) {
            $this->assertSame(1, $xpath->query("//*[@id='$field'][not(@required)]")->length);
            $this->assertStringNotContainsString('*', $xpath->query("//label[@for='$field']")->item(0)->textContent);
        }
    }
}
