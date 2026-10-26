<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_register_a_detailing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('owner.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get(route('owner.detailing.create'))->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->post(route('owner.detailing.store'), [
                'name' => 'Estética do Admin',
                'whatsapp' => '(19) 99999-0000',
                'postal_code' => '13025-320',
                'street' => 'Rua A',
                'number' => '1',
                'neighborhood' => 'Centro',
                'city' => 'Campinas',
                'state' => 'SP',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('automotive_detailings', 0);
    }

    public function test_header_shows_admin_link_only_for_admins(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertSee(route('admin.dashboard'));

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertDontSee(route('admin.dashboard'));
    }

    public function test_seeder_creates_documented_demo_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::where('email', 'admin@exemplo.test')->sole()->isAdmin());

        $statuses = [
            'aprovada@exemplo.test' => DetailingStatus::Approved,
            'pendente@exemplo.test' => DetailingStatus::Pending,
            'recusada@exemplo.test' => DetailingStatus::Rejected,
        ];

        foreach ($statuses as $email => $status) {
            $owner = User::where('email', $email)->sole();
            $this->assertFalse($owner->isAdmin());
            $this->assertSame($status, $owner->automotiveDetailing->status);
        }

        $this->assertNotNull(User::where('email', 'recusada@exemplo.test')->sole()->automotiveDetailing->rejection_reason);
        $this->assertSame(9, AutomotiveDetailing::approved()->count());
        $this->assertSame(1, AutomotiveDetailing::where('status', DetailingStatus::Inactive)->count());
        $this->assertSame(0, AutomotiveDetailing::whereNull('latitude')->count());
    }

    public function test_seeder_does_not_load_demo_data_in_production(): void
    {
        $this->app->instance('env', 'production');

        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->__invoke();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('automotive_detailings', 0);
    }

    public function test_not_found_page_is_presented_in_portuguese(): void
    {
        $this->get('/esteticas/estetica-inexistente')
            ->assertNotFound()
            ->assertSee('Página não encontrada')
            ->assertSee(route('detailings.index'));
    }

    public function test_forbidden_page_is_presented_in_portuguese(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Acesso não permitido');
    }

    public function test_conflict_page_explains_refused_transition(): void
    {
        $detailing = AutomotiveDetailing::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.detailings.approve', $detailing))
            ->assertStatus(409)
            ->assertSee('Esta alteração de situação não é permitida.');
    }

    public function test_home_search_submits_to_catalog(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('action="'.route('detailings.index').'"', false)
            ->assertSee('name="q"', false);
    }

    public function test_pages_offer_skip_link_and_labelled_fields(): void
    {
        $this->get(route('detailings.index'))
            ->assertSee('href="#conteudo"', false)
            ->assertSee('id="conteudo"', false)
            ->assertSee('<label for="q"', false);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'invalido', 'password' => ''])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="email-error"', false);
    }
}
