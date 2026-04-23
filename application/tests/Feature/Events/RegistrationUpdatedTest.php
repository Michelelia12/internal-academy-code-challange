<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationUpdated;
use App\Http\Controllers\RegistrationController;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\OverlapChecker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RegistrationUpdated::class)]
#[UsesClass(RegistrationController::class)]
#[UsesClass(WorkshopRegistration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
#[UsesClass(OverlapChecker::class)]
class RegistrationUpdatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function event_is_dispatched_when_employee_registers(): void
    {
        Event::fake();

        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 5]);

        $this->actingAs($employee)
            ->post("/workshops/{$workshop->id}/registrations");

        Event::assertDispatched(RegistrationUpdated::class, function (RegistrationUpdated $event) use ($workshop): bool {
            return $event->workshop->id === $workshop->id
                && $event->registration_count === 1;
        });
    }

    #[Test]
    public function event_is_dispatched_with_updated_count_after_cancellation(): void
    {
        Event::fake();

        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        RegistrationUpdated::dispatch($workshop, 0);

        Event::assertDispatched(RegistrationUpdated::class, function (RegistrationUpdated $event) use ($workshop): bool {
            return $event->workshop->id === $workshop->id
                && $event->registration_count === 0;
        });
    }

    #[Test]
    public function event_broadcasts_on_the_academy_channel(): void
    {
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->make();

        $event = new RegistrationUpdated($workshop, 3);
        $channels = $event->broadcastOn();

        $this::assertCount(1, $channels);
        $this::assertInstanceOf(Channel::class, $channels[0]);
        $this::assertSame('academy', $channels[0]->name);
    }
}
