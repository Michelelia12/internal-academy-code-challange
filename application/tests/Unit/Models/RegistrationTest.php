<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(Registration::class)]
#[CoversClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function confirmed_status_has_correct_value(): void
    {
        $this::assertSame('confirmed', RegistrationStatus::Confirmed->value);
    }

    #[Test]
    public function waiting_status_has_correct_value(): void
    {
        $this::assertSame('waiting', RegistrationStatus::Waiting->value);
    }

    #[Test]
    public function it_casts_status_to_registration_status_enum(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        /** @var Registration $registration */
        $registration = Registration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => 'confirmed',
            'position' => null,
        ]);

        $this::assertInstanceOf(RegistrationStatus::class, $registration->status);
        $this::assertSame(RegistrationStatus::Confirmed, $registration->status);
    }

    #[Test]
    public function it_casts_waiting_status_to_enum(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        /** @var Registration $registration */
        $registration = Registration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => 'waiting',
            'position' => 1,
        ]);

        $this::assertSame(RegistrationStatus::Waiting, $registration->status);
        $this::assertSame(1, $registration->position);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        /** @var Registration $registration */
        $registration = Registration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => 'confirmed',
            'position' => null,
        ]);

        $this::assertInstanceOf(BelongsTo::class, $registration->user());
        $loadedUser = $registration->user;
        $this::assertInstanceOf(User::class, $loadedUser);
        $this::assertSame($user->id, $loadedUser->id);
    }

    #[Test]
    public function it_belongs_to_a_workshop(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        /** @var Registration $registration */
        $registration = Registration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => 'confirmed',
            'position' => null,
        ]);

        $this::assertInstanceOf(BelongsTo::class, $registration->workshop());
        $loadedWorkshop = $registration->workshop;
        $this::assertInstanceOf(Workshop::class, $loadedWorkshop);
        $this::assertSame($workshop->id, $loadedWorkshop->id);
    }
}
