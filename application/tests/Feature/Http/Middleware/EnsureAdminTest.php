<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(EnsureAdmin::class)]
#[UsesClass(User::class)]
class EnsureAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/admin-only', fn () => 'ok')->middleware(['auth', 'admin']);
    }

    #[Test]
    public function admin_can_access_admin_route(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get('/admin-only')->assertStatus(200);
    }

    #[Test]
    public function employee_is_blocked_from_admin_route(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();

        $this->actingAs($user)->get('/admin-only')->assertStatus(403);
    }

    #[Test]
    public function guest_is_redirected_to_login_from_admin_route(): void
    {
        $this->get('/admin-only')->assertRedirect('/login');
    }

    #[Test]
    public function admin_gate_allows_admin_user(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        $this::assertTrue(Gate::forUser($user)->allows('admin'));
    }

    #[Test]
    public function admin_gate_denies_employee(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();

        $this::assertFalse(Gate::forUser($user)->allows('admin'));
    }
}
