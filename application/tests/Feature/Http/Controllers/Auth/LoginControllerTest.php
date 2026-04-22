<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(LoginController::class)]
#[UsesClass(User::class)]
class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_accessible_to_guests(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('data-page="app"', false)
            ->assertSee('"component":"Auth\\/Login"', false);
    }

    #[Test]
    public function valid_credentials_log_the_user_in(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function wrong_password_is_rejected(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function unknown_email_is_rejected(): void
    {
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function login_requires_email_and_password(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);
    }

    #[Test]
    public function authenticated_user_can_log_out(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
