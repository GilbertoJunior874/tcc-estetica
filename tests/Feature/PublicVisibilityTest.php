<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public static function hiddenStatuses(): array
    {
        return [
            'pending' => [DetailingStatus::Pending],
            'rejected' => [DetailingStatus::Rejected],
            'inactive' => [DetailingStatus::Inactive],
        ];
    }

    public function test_approved_detailing_is_public(): void
    {
        $detailing = AutomotiveDetailing::factory()->for(User::factory(), 'owner')->create();

        $this->get(route('home'))->assertSee($detailing->name);
        $this->get(route('detailings.index'))->assertSee($detailing->name);
        $this->get(route('detailings.show', $detailing))->assertOk()->assertSee($detailing->name);
    }

    #[DataProvider('hiddenStatuses')]
    public function test_non_approved_detailing_is_hidden(DetailingStatus $status): void
    {
        $detailing = AutomotiveDetailing::factory()->for(User::factory(), 'owner')->create(['status' => $status]);

        $this->get(route('home'))->assertDontSee($detailing->name);
        $this->get(route('detailings.index'))->assertDontSee($detailing->name);
        $this->get(route('detailings.show', $detailing))->assertNotFound();
    }

    public function test_only_active_services_are_public(): void
    {
        $detailing = AutomotiveDetailing::factory()->create();
        Service::factory()->for($detailing)->create(['name' => 'Lavagem ativa']);
        Service::factory()->for($detailing)->inactive()->create(['name' => 'Serviço pausado']);

        $this->get(route('detailings.show', $detailing))
            ->assertSee('Lavagem ativa')
            ->assertDontSee('Serviço pausado');

        $this->get(route('detailings.index'))
            ->assertSee('Lavagem ativa')
            ->assertDontSee('Serviço pausado');
    }

    public function test_service_deactivated_by_owner_leaves_public_page(): void
    {
        $user = User::factory()->create();
        $detailing = AutomotiveDetailing::factory()->for($user, 'owner')->create();
        $service = Service::factory()->for($detailing)->create(['name' => 'Higienização completa']);

        $this->get(route('detailings.show', $detailing))->assertSee('Higienização completa');

        $this->actingAs($user)->patch(route('owner.services.toggle', $service));

        $this->get(route('detailings.show', $detailing))->assertDontSee('Higienização completa');
    }

    public function test_development_seed_covers_owner_flows(): void
    {
        $this->seed();

        $approvedOwner = User::firstWhere('email', 'aprovada@exemplo.test');
        $pendingOwner = User::firstWhere('email', 'pendente@exemplo.test');

        $this->assertTrue($approvedOwner->automotiveDetailing->isApproved());
        $this->assertSame(DetailingStatus::Pending, $pendingOwner->automotiveDetailing->status);
        $this->assertTrue(Service::where('active', false)->exists());
        $this->assertSame(7, $approvedOwner->automotiveDetailing->businessHours()->count());
        $this->assertGreaterThanOrEqual(8, AutomotiveDetailing::approved()->count());
        $this->assertSame(0, BusinessHour::whereNull('opens_at')->where('closed', false)->count());
    }
}
