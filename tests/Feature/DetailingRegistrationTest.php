<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
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
}
