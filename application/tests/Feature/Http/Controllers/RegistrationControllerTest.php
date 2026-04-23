<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationUpdated;
use App\Http\Controllers\RegistrationController;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use App\Services\OverlapChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RegistrationController::class)]
#[UsesClass(Registration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(RegistrationUpdated::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
#[UsesClass(OverlapChecker::class)]
class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function employee_is_registered_as_confirmed_when_seats_are_available(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 5]);

        $response = $this->actingAs($employee)
            ->post("/workshops/{$workshop->id}/registrations");

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed->value,
            'position' => null,
        ]);
    }

    #[Test]
    public function employee_is_added_to_waiting_list_when_workshop_is_full(): void
    {
        /** @var User $other */
        $other = User::factory()->employee()->create();
        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 1]);

        Registration::create([
            'user_id' => $other->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        $response = $this->actingAs($employee)
            ->post("/workshops/{$workshop->id}/registrations");

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting->value,
            'position' => 1,
        ]);
    }

    #[Test]
    public function employee_cannot_register_twice_for_the_same_workshop(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 5]);

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        $this->actingAs($employee)
            ->post("/workshops/{$workshop->id}/registrations");

        $this->assertDatabaseCount('registrations', 1);
    }

    #[Test]
    public function employee_cannot_register_for_overlapping_workshop(): void
    {
        /** @var User $employee */
        $employee = User::factory()->employee()->create();

        /** @var Workshop $existing */
        $existing = Workshop::factory()->create([
            'starts_at' => '2030-06-01 09:00:00',
            'ends_at' => '2030-06-01 11:00:00',
        ]);
        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $existing->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        /** @var Workshop $overlapping */
        $overlapping = Workshop::factory()->create([
            'starts_at' => '2030-06-01 10:00:00',
            'ends_at' => '2030-06-01 12:00:00',
        ]);

        $response = $this->actingAs($employee)
            ->post("/workshops/{$overlapping->id}/registrations");

        $response->assertStatus(422);
        $this->assertDatabaseCount('registrations', 1);
    }

    #[Test]
    public function guest_is_redirected_to_login_by_middleware(): void
    {
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $response = $this->post("/workshops/{$workshop->id}/registrations");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function controller_redirects_to_login_when_request_has_no_user(): void
    {
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        $response = $this->withoutMiddleware()
            ->post("/workshops/{$workshop->id}/registrations");

        $response->assertRedirect(route('login'));
    }
}
