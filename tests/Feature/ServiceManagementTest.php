<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(array $attributes = []): User
    {
        $user = User::factory()->create();
        AutomotiveDetailing::factory()->for($user, 'owner')->create($attributes);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Polimento comercial',
            'description' => 'Polimento em etapa única com acabamento.',
            'price' => '349,90',
            'duration_minutes' => 180,
            'active' => '1',
        ], $overrides);
    }

    public function test_owner_sees_only_own_services(): void
    {
        $user = $this->owner();
        Service::factory()->for($user->automotiveDetailing)->create(['name' => 'Lavagem da casa']);
        Service::factory()->create(['name' => 'Serviço de outra estética']);

        $this->actingAs($user)
            ->get(route('owner.services.index'))
            ->assertOk()
            ->assertSee('Lavagem da casa')
            ->assertDontSee('Serviço de outra estética');
    }

    public function test_owner_can_create_service(): void
    {
        $user = $this->owner();

        $this->actingAs($user)->get(route('owner.services.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('owner.services.store'), $this->payload())
            ->assertRedirect(route('owner.services.index'))
            ->assertSessionHasNoErrors();

        $service = $user->automotiveDetailing->services()->sole();

        $this->assertSame('Polimento comercial', $service->name);
        $this->assertSame(34990, $service->price_cents);
        $this->assertSame(180, $service->duration_minutes);
        $this->assertTrue($service->active);
    }

    public function test_price_formats_are_converted_to_cents(): void
    {
        $user = $this->owner();

        foreach ([['80', 8000], ['80,5', 8050], ['99.90', 9990], ['1.250,00', 125000], ['1.250', 125000]] as [$price, $cents]) {
            $this->actingAs($user)->post(route('owner.services.store'), $this->payload(['price' => $price]));

            $this->assertSame($cents, $user->automotiveDetailing->services()->latest('id')->value('price_cents'), $price);
        }
    }

    public function test_service_validation(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('owner.services.store'), $this->payload(['name' => '', 'price' => 'abc', 'duration_minutes' => 0]))
            ->assertSessionHasErrors(['name', 'price', 'duration_minutes']);

        $this->actingAs($user)
            ->post(route('owner.services.store'), $this->payload(['price' => '0,00']))
            ->assertSessionHasErrors('price');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_owner_can_update_service(): void
    {
        $user = $this->owner();
        $service = Service::factory()->for($user->automotiveDetailing)->create();

        $this->actingAs($user)->get(route('owner.services.edit', $service))->assertOk();

        $this->actingAs($user)
            ->put(route('owner.services.update', $service), $this->payload(['name' => 'Polimento atualizado', 'price' => '400', 'duration_minutes' => null]))
            ->assertRedirect(route('owner.services.index'));

        $service->refresh();

        $this->assertSame('Polimento atualizado', $service->name);
        $this->assertSame(40000, $service->price_cents);
        $this->assertNull($service->duration_minutes);
    }

    public function test_owner_can_toggle_service(): void
    {
        $user = $this->owner();
        $service = Service::factory()->for($user->automotiveDetailing)->create();

        $this->actingAs($user)->patch(route('owner.services.toggle', $service))->assertRedirect(route('owner.services.index'));
        $this->assertFalse($service->fresh()->active);

        $this->actingAs($user)->patch(route('owner.services.toggle', $service));
        $this->assertTrue($service->fresh()->active);
    }

    public function test_owner_can_delete_service(): void
    {
        $user = $this->owner();
        $service = Service::factory()->for($user->automotiveDetailing)->create();

        $this->actingAs($user)
            ->delete(route('owner.services.destroy', $service))
            ->assertRedirect(route('owner.services.index'));

        $this->assertModelMissing($service);
    }

    public function test_owner_cannot_manage_services_of_another_detailing(): void
    {
        $userA = $this->owner();
        $foreign = Service::factory()->for($this->owner()->automotiveDetailing)->create(['name' => 'Original', 'active' => true]);

        $this->actingAs($userA)->get(route('owner.services.edit', $foreign))->assertForbidden();
        $this->actingAs($userA)->put(route('owner.services.update', $foreign), $this->payload())->assertForbidden();
        $this->actingAs($userA)->patch(route('owner.services.toggle', $foreign))->assertForbidden();
        $this->actingAs($userA)->delete(route('owner.services.destroy', $foreign))->assertForbidden();

        $foreign->refresh();

        $this->assertModelExists($foreign);
        $this->assertSame('Original', $foreign->name);
        $this->assertTrue($foreign->active);
    }

    public function test_store_always_uses_owner_detailing(): void
    {
        $user = $this->owner();
        $other = $this->owner();

        $this->actingAs($user)->post(route('owner.services.store'), $this->payload([
            'automotive_detailing_id' => $other->automotiveDetailing->id,
        ]));

        $this->assertSame(1, $user->automotiveDetailing->services()->count());
        $this->assertSame(0, $other->automotiveDetailing->services()->count());
    }

    public function test_inactive_detailing_cannot_manage_services(): void
    {
        $user = $this->owner(['status' => DetailingStatus::Inactive]);
        $service = Service::factory()->for($user->automotiveDetailing)->create();

        $this->actingAs($user)->get(route('owner.services.index'))->assertOk()->assertDontSee(route('owner.services.create'));
        $this->actingAs($user)->post(route('owner.services.store'), $this->payload())->assertForbidden();
        $this->actingAs($user)->delete(route('owner.services.destroy', $service))->assertForbidden();
    }

    public function test_services_require_registered_detailing(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('owner.services.index'))
            ->assertRedirect(route('owner.detailing.create'));
    }
}
