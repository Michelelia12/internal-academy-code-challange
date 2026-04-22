<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\DashboardController;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(DashboardController::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_employee_sees_future_workshops(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();

        Workshop::factory()->create([
            'title' => 'Future Workshop',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(2),
        ]);

        $this->actingAs($employee)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('data-page="app"', false)
            ->assertSee('"component":"Dashboard"', false)
            ->assertSee('Future Workshop', false);
    }

    #[Test]
    public function past_workshops_are_not_included(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();

        Workshop::factory()->create([
            'title' => 'Past Workshop',
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->subDays(7)->addHours(2),
        ]);

        $this->actingAs($employee)->get('/dashboard')
            ->assertStatus(200)
            ->assertDontSee('Past Workshop', false);
    }

    #[Test]
    public function future_workshops_are_ordered_by_starts_at(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();

        Workshop::factory()->create([
            'title' => 'Later Workshop',
            'starts_at' => now()->addDays(14),
            'ends_at' => now()->addDays(14)->addHours(2),
        ]);

        Workshop::factory()->create([
            'title' => 'Sooner Workshop',
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
        ]);

        $this->actingAs($employee)->get('/dashboard')
            ->assertSeeInOrder(['Sooner Workshop', 'Later Workshop'], false);
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
