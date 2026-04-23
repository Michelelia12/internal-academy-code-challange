<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(StatisticsController::class)]
#[UsesClass(EnsureAdmin::class)]
#[UsesClass(Registration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class StatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_sees_the_most_popular_workshop(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var Workshop $popular */
        $popular = Workshop::factory()->create(['title' => 'Popular Workshop']);
        /** @var Workshop $other */
        $other = Workshop::factory()->create(['title' => 'Other Workshop']);
        /** @var User $u1 */
        $u1 = User::factory()->employee()->create();
        /** @var User $u2 */
        $u2 = User::factory()->employee()->create();
        /** @var User $u3 */
        $u3 = User::factory()->employee()->create();

        Registration::create(['user_id' => $u1->id, 'workshop_id' => $popular->id, 'status' => RegistrationStatus::Confirmed, 'position' => null]);
        Registration::create(['user_id' => $u2->id, 'workshop_id' => $popular->id, 'status' => RegistrationStatus::Confirmed, 'position' => null]);
        Registration::create(['user_id' => $u3->id, 'workshop_id' => $other->id, 'status' => RegistrationStatus::Confirmed, 'position' => null]);

        $this->actingAs($admin)->get('/admin/statistics')
            ->assertStatus(200)
            ->assertSee('Popular Workshop', false);
    }

    #[Test]
    public function admin_sees_the_correct_total_registration_count(): void
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();
        /** @var User $u1 */
        $u1 = User::factory()->employee()->create();
        /** @var User $u2 */
        $u2 = User::factory()->employee()->create();
        /** @var User $u3 */
        $u3 = User::factory()->employee()->create();

        Registration::create(['user_id' => $u1->id, 'workshop_id' => $workshop->id, 'status' => RegistrationStatus::Confirmed, 'position' => null]);
        Registration::create(['user_id' => $u2->id, 'workshop_id' => $workshop->id, 'status' => RegistrationStatus::Confirmed, 'position' => null]);
        Registration::create(['user_id' => $u3->id, 'workshop_id' => $workshop->id, 'status' => RegistrationStatus::Waiting, 'position' => 1]);

        $this->actingAs($admin)->get('/admin/statistics')
            ->assertStatus(200)
            ->assertSee('"total_count":2', false);
    }

    #[Test]
    public function employee_is_blocked(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();

        $this->actingAs($employee)->get('/admin/statistics')
            ->assertStatus(403);
    }

    #[Test]
    public function guest_is_blocked(): void
    {
        $this->get('/admin/statistics')->assertStatus(403);
    }
}
