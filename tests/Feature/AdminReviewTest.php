<?php

namespace Tests\Feature;

use App\Enums\DetailingStatus;
use App\Models\AutomotiveDetailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function pendingDetailing(array $attributes = []): AutomotiveDetailing
    {
        return AutomotiveDetailing::factory()->for(User::factory(), 'owner')->pending()->create($attributes);
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['admin.dashboard', false],
            'listagem' => ['admin.detailings.index', false],
            'detalhe' => ['admin.detailings.show', true],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_guest_is_redirected_to_login(string $route, bool $needsDetailing): void
    {
        $parameters = $needsDetailing ? [$this->pendingDetailing()] : [];

        $this->get(route($route, $parameters))->assertRedirect(route('login'));
    }

    #[DataProvider('adminPages')]
    public function test_regular_user_is_forbidden(string $route, bool $needsDetailing): void
    {
        $parameters = $needsDetailing ? [$this->pendingDetailing()] : [];

        $this->actingAs(User::factory()->create())
            ->get(route($route, $parameters))
            ->assertForbidden();
    }

    public static function adminActions(): array
    {
        return [
            'aprovar' => ['admin.detailings.approve', DetailingStatus::Pending],
            'recusar' => ['admin.detailings.reject', DetailingStatus::Pending],
            'desativar' => ['admin.detailings.deactivate', DetailingStatus::Approved],
            'reativar' => ['admin.detailings.reactivate', DetailingStatus::Inactive],
        ];
    }

    #[DataProvider('adminActions')]
    public function test_owner_cannot_call_admin_actions_on_own_detailing(string $route, DetailingStatus $status): void
    {
        $owner = User::factory()->create();
        $detailing = AutomotiveDetailing::factory()->for($owner, 'owner')->create(['status' => $status]);

        $this->actingAs($owner)
            ->patch(route($route, $detailing), ['rejection_reason' => 'Tentativa de alteração indevida.'])
            ->assertForbidden();

        $this->assertSame($status, $detailing->fresh()->status);
    }

    #[DataProvider('adminActions')]
    public function test_guest_cannot_call_admin_actions(string $route, DetailingStatus $status): void
    {
        $detailing = AutomotiveDetailing::factory()->create(['status' => $status]);

        $this->patch(route($route, $detailing))->assertRedirect(route('login'));

        $this->assertSame($status, $detailing->fresh()->status);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->admin();

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_shows_counts_and_pending_requests(): void
    {
        $this->pendingDetailing(['name' => 'Solicitação Aguardando']);
        AutomotiveDetailing::factory()->count(2)->create();
        AutomotiveDetailing::factory()->rejected()->create();
        AutomotiveDetailing::factory()->inactive()->count(3)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Solicitação Aguardando');

        $this->assertSame(
            ['pending' => 1, 'approved' => 2, 'rejected' => 1, 'inactive' => 3],
            $response->viewData('counts')->all(),
        );
    }

    public function test_listing_shows_owner_and_filters_by_status(): void
    {
        $owner = User::factory()->create(['name' => 'Maria Responsável', 'email' => 'maria@exemplo.test']);
        AutomotiveDetailing::factory()->for($owner, 'owner')->pending()->create(['name' => 'Estética Pendente']);
        AutomotiveDetailing::factory()->create(['name' => 'Estética Aprovada']);

        $this->actingAs($this->admin())
            ->get(route('admin.detailings.index'))
            ->assertOk()
            ->assertSee('Estética Pendente')
            ->assertSee('Estética Aprovada')
            ->assertSee('Maria Responsável')
            ->assertSee('maria@exemplo.test');

        $this->actingAs($this->admin())
            ->get(route('admin.detailings.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Estética Pendente')
            ->assertDontSee('Estética Aprovada');
    }

    public function test_listing_ignores_unknown_status_filter(): void
    {
        AutomotiveDetailing::factory()->create(['name' => 'Estética Aprovada']);

        $this->actingAs($this->admin())
            ->get(route('admin.detailings.index', ['status' => "x' or 1=1"]))
            ->assertOk()
            ->assertSee('Estética Aprovada');
    }

    public function test_detail_shows_full_information(): void
    {
        $detailing = $this->pendingDetailing(['name' => 'Estúdio em Análise', 'whatsapp' => '(19) 98888-7777']);
        $detailing->services()->create(['name' => 'Polimento oculto', 'price_cents' => 50000, 'active' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.detailings.show', $detailing))
            ->assertOk()
            ->assertSee('Estúdio em Análise')
            ->assertSee('(19) 98888-7777')
            ->assertSee($detailing->owner->email)
            ->assertSee('Polimento oculto')
            ->assertSee('Aprovar e publicar')
            ->assertSee('Recusar solicitação');
    }

    public function test_admin_approves_pending_detailing(): void
    {
        $admin = $this->admin();
        $detailing = $this->pendingDetailing();

        $this->actingAs($admin)
            ->patch(route('admin.detailings.approve', $detailing))
            ->assertRedirect(route('admin.detailings.show', $detailing));

        $detailing->refresh();
        $this->assertSame(DetailingStatus::Approved, $detailing->status);
        $this->assertSame($admin->id, $detailing->reviewed_by);
        $this->assertNotNull($detailing->reviewed_at);

        $this->get(route('detailings.show', $detailing))->assertOk();
    }

    public function test_admin_rejects_with_reason(): void
    {
        $detailing = $this->pendingDetailing();

        $this->actingAs($this->admin())
            ->patch(route('admin.detailings.reject', $detailing), ['rejection_reason' => 'Endereço informado não existe.'])
            ->assertRedirect(route('admin.detailings.show', $detailing));

        $detailing->refresh();
        $this->assertSame(DetailingStatus::Rejected, $detailing->status);
        $this->assertSame('Endereço informado não existe.', $detailing->rejection_reason);
    }

    public function test_rejection_requires_reason(): void
    {
        $detailing = $this->pendingDetailing();

        $this->actingAs($this->admin())
            ->from(route('admin.detailings.show', $detailing))
            ->patch(route('admin.detailings.reject', $detailing), ['rejection_reason' => ''])
            ->assertRedirect(route('admin.detailings.show', $detailing))
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(DetailingStatus::Pending, $detailing->fresh()->status);
    }

    public function test_owner_sees_rejection_reason_and_can_resubmit(): void
    {
        $detailing = $this->pendingDetailing();

        $this->actingAs($this->admin())
            ->patch(route('admin.detailings.reject', $detailing), ['rejection_reason' => 'Falta o número do endereço.']);

        $owner = $detailing->owner;

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertSee(DetailingStatus::Rejected->label())
            ->assertSee('Falta o número do endereço.');

        $this->actingAs($owner)
            ->put(route('owner.detailing.update'), [
                ...$detailing->only(['name', 'description', 'whatsapp', 'postal_code', 'street', 'neighborhood', 'city', 'state']),
                'number' => '250',
            ])
            ->assertRedirect(route('owner.dashboard'));

        $detailing->refresh();
        $this->assertSame(DetailingStatus::Pending, $detailing->status);
        $this->assertNull($detailing->rejection_reason);
        $this->assertSame('250', $detailing->number);
    }

    public function test_admin_deactivates_and_detailing_leaves_catalog(): void
    {
        $detailing = AutomotiveDetailing::factory()->create(['name' => 'Estética Que Sai']);

        $this->get(route('detailings.index'))->assertSee('Estética Que Sai');

        $this->actingAs($this->admin())
            ->patch(route('admin.detailings.deactivate', $detailing))
            ->assertRedirect(route('admin.detailings.show', $detailing));

        $this->assertSame(DetailingStatus::Inactive, $detailing->fresh()->status);

        auth()->logout();

        $this->get(route('detailings.index'))->assertDontSee('Estética Que Sai');
        $this->get(route('detailings.show', $detailing))->assertNotFound();
    }

    public function test_admin_reactivates_inactive_detailing(): void
    {
        $detailing = AutomotiveDetailing::factory()->inactive()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.detailings.reactivate', $detailing))
            ->assertRedirect(route('admin.detailings.show', $detailing));

        $this->assertSame(DetailingStatus::Approved, $detailing->fresh()->status);
    }

    public static function invalidTransitions(): array
    {
        return [
            'aprovar já aprovada' => ['admin.detailings.approve', DetailingStatus::Approved],
            'aprovar recusada' => ['admin.detailings.approve', DetailingStatus::Rejected],
            'aprovar inativa' => ['admin.detailings.approve', DetailingStatus::Inactive],
            'recusar aprovada' => ['admin.detailings.reject', DetailingStatus::Approved],
            'recusar inativa' => ['admin.detailings.reject', DetailingStatus::Inactive],
            'desativar pendente' => ['admin.detailings.deactivate', DetailingStatus::Pending],
            'desativar recusada' => ['admin.detailings.deactivate', DetailingStatus::Rejected],
            'reativar pendente' => ['admin.detailings.reactivate', DetailingStatus::Pending],
            'reativar recusada' => ['admin.detailings.reactivate', DetailingStatus::Rejected],
        ];
    }

    #[DataProvider('invalidTransitions')]
    public function test_invalid_transitions_are_refused(string $route, DetailingStatus $status): void
    {
        $detailing = AutomotiveDetailing::factory()->create(['status' => $status]);

        $this->actingAs($this->admin())
            ->patch(route($route, $detailing), ['rejection_reason' => 'Motivo qualquer para a recusa.'])
            ->assertStatus(409);

        $this->assertSame($status, $detailing->fresh()->status);
    }

    public function test_model_refuses_invalid_transition(): void
    {
        $detailing = AutomotiveDetailing::factory()->rejected()->create();

        $this->expectException(LogicException::class);

        $detailing->transitionTo(DetailingStatus::Approved, $this->admin());
    }

    public function test_admin_flags_cannot_be_mass_assigned(): void
    {
        $this->post(route('register'), [
            'name' => 'Pessoa Comum',
            'email' => 'pessoa@exemplo.test',
            'password' => 'senha-segura-123',
            'password_confirmation' => 'senha-segura-123',
            'is_admin' => '1',
        ]);

        $this->assertFalse(User::where('email', 'pessoa@exemplo.test')->sole()->isAdmin());
    }

    public function test_owner_cannot_change_status_through_detailing_form(): void
    {
        $detailing = $this->pendingDetailing();

        $this->actingAs($detailing->owner)
            ->put(route('owner.detailing.update'), [
                ...$detailing->only(['name', 'whatsapp', 'postal_code', 'street', 'number', 'neighborhood', 'city', 'state']),
                'status' => 'approved',
                'reviewed_by' => 1,
            ]);

        $detailing->refresh();
        $this->assertSame(DetailingStatus::Pending, $detailing->status);
        $this->assertNull($detailing->reviewed_by);
    }

    public function test_admin_endpoints_require_csrf_token(): void
    {
        $detailing = $this->pendingDetailing();

        $this->app->instance('env', 'local');

        $this->actingAs($this->admin())
            ->patch(route('admin.detailings.approve', $detailing))
            ->assertStatus(419);

        $this->assertSame(DetailingStatus::Pending, $detailing->fresh()->status);
    }
}
