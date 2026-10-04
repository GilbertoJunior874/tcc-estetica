<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_register(): void
    {
        $this->get(route('register'))->assertOk();

        $this->post(route('register'), [
            'name' => 'Marina Duarte',
            'email' => 'marina@example.com',
            'password' => 'senha-segura-123',
            'password_confirmation' => 'senha-segura-123',
        ])->assertRedirect(route('owner.detailing.create'));

        $user = User::firstWhere('email', 'marina@example.com');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('senha-segura-123', $user->password));
    }

    public function test_registration_requires_valid_data(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register'), [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'curta',
            'password_confirmation' => 'diferente',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_owner_can_login(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'))->assertOk();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'senha-errada',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_owner_area_requires_authentication(): void
    {
        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('owner.dashboard'));
    }
}
