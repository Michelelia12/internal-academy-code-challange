<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RegisterController::class)]
#[UsesClass(User::class)]
class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function register_page_is_accessible_to_guests(): void
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('data-page="app"', false)
            ->assertSee('"component":"Auth\\/Register"', false);
    }

    #[Test]
    public function new_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    #[Test]
    public function password_is_hashed_on_registration(): void
    {
        $this->post('/register', [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        /** @var User $user */
        $user = User::query()->where('email', 'bob@example.com')->firstOrFail();

        $this::assertNotSame('password123', $user->password);
        $this::assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function registration_validates_required_fields(): void
    {
        $this->post('/register', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function is_admin_field_is_ignored_on_registration(): void
    {
        $this->post('/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => true,
        ]);

        /** @var User $user */
        $user = User::query()->where('email', 'hacker@example.com')->firstOrFail();

        $this::assertFalse($user->is_admin);
    }
}
