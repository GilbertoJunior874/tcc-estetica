<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\BusinessHour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWithDetailing(array $attributes = []): User
    {
        $user = User::factory()->create();
        AutomotiveDetailing::factory()->for($user, 'owner')->pending()->create($attributes);

        return $user;
    }

    private function detailingPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Novo Nome Estética',
            'description' => 'Descrição atualizada.',
            'phone' => null,
            'whatsapp' => '(19) 99111-2233',
            'email' => null,
            'postal_code' => '13087000',
            'street' => 'Avenida Heitor Penteado',
            'number' => '100',
            'complement' => 'Sala 2',
            'neighborhood' => 'Taquaral',
            'city' => 'Campinas',
            'state' => 'SP',
        ], $overrides);
    }

    private function hoursPayload(array $overrides = []): array
    {
        $hours = collect(BusinessHour::DAYS)->keys()
            ->mapWithKeys(fn (int $day) => [$day => ['opens_at' => '08:00', 'closes_at' => '18:00']])
            ->all();

        $hours[7] = ['closed' => '1'];

        return ['hours' => array_replace($hours, $overrides)];
    }

    public function test_dashboard_shows_status_and_shortcuts(): void
    {
        $user = $this->ownerWithDetailing(['name' => 'Estúdio Polido']);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Estúdio Polido')
            ->assertSee(DetailingStatus::Pending->label())
            ->assertSee(route('owner.detailing.edit'))
            ->assertSee(route('owner.hours.edit'));
    }

    public function test_rejected_detailing_shows_reason_on_dashboard(): void
    {
        $user = $this->ownerWithDetailing(['status' => DetailingStatus::Rejected, 'rejection_reason' => 'Endereço não confere.']);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertSee(DetailingStatus::Rejected->label())
            ->assertSee('Endereço não confere.');
    }

    public function test_owner_can_update_own_detailing(): void
    {
        $user = $this->ownerWithDetailing();
        $slug = $user->automotiveDetailing->slug;

        $this->actingAs($user)->get(route('owner.detailing.edit'))->assertOk();

        $this->actingAs($user)
            ->put(route('owner.detailing.update'), $this->detailingPayload())
            ->assertRedirect(route('owner.detailing.edit'))
            ->assertSessionHasNoErrors();

        $detailing = $user->automotiveDetailing->fresh();

        $this->assertSame('Novo Nome Estética', $detailing->name);
        $this->assertSame('Sala 2', $detailing->complement);
        $this->assertSame($slug, $detailing->slug);
    }

    public function test_owner_cannot_change_status_or_ownership_when_updating(): void
    {
        $user = $this->ownerWithDetailing();
        $other = User::factory()->create();

        $this->actingAs($user)->put(route('owner.detailing.update'), $this->detailingPayload([
            'status' => DetailingStatus::Approved->value,
            'user_id' => $other->id,
        ]));

        $detailing = $user->automotiveDetailing->fresh();

        $this->assertSame(DetailingStatus::Pending, $detailing->status);
        $this->assertSame($user->id, $detailing->user_id);
    }

    public function test_owner_only_updates_own_detailing(): void
    {
        $userA = $this->ownerWithDetailing(['name' => 'Estética A']);
        $userB = $this->ownerWithDetailing(['name' => 'Estética B']);

        $this->actingAs($userA)->put(route('owner.detailing.update'), $this->detailingPayload([
            'id' => $userB->automotiveDetailing->id,
        ]));

        $this->assertSame('Estética B', $userB->automotiveDetailing->fresh()->name);
        $this->assertSame('Novo Nome Estética', $userA->automotiveDetailing->fresh()->name);
    }

    public function test_inactive_detailing_cannot_be_edited(): void
    {
        $user = $this->ownerWithDetailing(['status' => DetailingStatus::Inactive, 'name' => 'Bloqueada']);

        $this->actingAs($user)->get(route('owner.detailing.edit'))->assertForbidden();
        $this->actingAs($user)->put(route('owner.detailing.update'), $this->detailingPayload())->assertForbidden();
        $this->actingAs($user)->put(route('owner.hours.update'), $this->hoursPayload())->assertForbidden();

        $this->assertSame('Bloqueada', $user->automotiveDetailing->fresh()->name);
    }

    public function test_user_without_detailing_is_sent_to_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('owner.detailing.edit'))
            ->assertRedirect(route('owner.detailing.create'));
    }

    public function test_detailing_update_is_validated(): void
    {
        $user = $this->ownerWithDetailing();

        $this->actingAs($user)
            ->put(route('owner.detailing.update'), $this->detailingPayload(['name' => '', 'whatsapp' => '']))
            ->assertSessionHasErrors(['name', 'whatsapp']);
    }

    public function test_owner_can_save_business_hours(): void
    {
        $user = $this->ownerWithDetailing();

        $this->actingAs($user)->get(route('owner.hours.edit'))->assertOk()->assertSee('Segunda-feira');

        $this->actingAs($user)
            ->put(route('owner.hours.update'), $this->hoursPayload([6 => ['opens_at' => '09:00', 'closes_at' => '13:00']]))
            ->assertRedirect(route('owner.hours.edit'))
            ->assertSessionHasNoErrors();

        $hours = $user->automotiveDetailing->businessHours()->get()->keyBy('day_of_week');

        $this->assertCount(7, $hours);
        $this->assertSame('09:00', $hours[6]->opens_at);
        $this->assertSame('13:00', $hours[6]->closes_at);
        $this->assertTrue($hours[7]->closed);
        $this->assertNull($hours[7]->opens_at);

        $this->actingAs($user)->put(route('owner.hours.update'), $this->hoursPayload([1 => ['closed' => '1']]));

        $this->assertTrue($user->automotiveDetailing->businessHours()->where('day_of_week', 1)->sole()->closed);
        $this->assertDatabaseCount('business_hours', 7);
    }

    public function test_invalid_business_hours_are_rejected(): void
    {
        $user = $this->ownerWithDetailing();

        $this->actingAs($user)
            ->put(route('owner.hours.update'), $this->hoursPayload([
                1 => ['opens_at' => '18:00', 'closes_at' => '08:00'],
                2 => ['opens_at' => '25:00', 'closes_at' => '18:00'],
                3 => ['opens_at' => '', 'closes_at' => ''],
            ]))
            ->assertSessionHasErrors(['hours.1.closes_at', 'hours.2.opens_at', 'hours.3.opens_at', 'hours.3.closes_at']);

        $this->assertDatabaseCount('business_hours', 0);
    }

    public function test_business_hours_appear_on_public_page(): void
    {
        $detailing = AutomotiveDetailing::factory()->create();
        BusinessHour::factory()->for($detailing)->create(['day_of_week' => 1, 'opens_at' => '07:30', 'closes_at' => '17:00']);
        BusinessHour::factory()->for($detailing)->closed()->create(['day_of_week' => 7]);

        $this->get(route('detailings.show', $detailing))
            ->assertOk()
            ->assertSee('07:30 – 17:00')
            ->assertSee('Fechado');
    }
}
