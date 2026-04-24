<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversNothing]
class SharedPropsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_receives_auth_user_prop(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create(['name' => 'Test Employee']);

        $this->actingAs($employee)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('"name":"Test Employee"', false)
            ->assertSee('"is_admin":false', false);
    }

    #[Test]
    public function unauthenticated_request_receives_null_auth_user(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('"auth":{"user":null}', false);
    }
}
