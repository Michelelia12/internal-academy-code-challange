<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\RegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\OverlapChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(OverlapChecker::class)]
#[UsesClass(WorkshopRegistration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class OverlapCheckerTest extends TestCase
{
    use RefreshDatabase;

    private OverlapChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new OverlapChecker;
    }

    #[Test]
    public function it_detects_a_full_overlap(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $existing */
        $existing = Workshop::factory()->create([
            'starts_at' => '2030-06-01 09:00:00',
            'ends_at' => '2030-06-01 11:00:00',
        ]);
        WorkshopRegistration::create([
            'user_id' => $user->id,
            'workshop_id' => $existing->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        /** @var Workshop $target */
        $target = Workshop::factory()->create([
            'starts_at' => '2030-06-01 10:00:00',
            'ends_at' => '2030-06-01 12:00:00',
        ]);

        $this::assertTrue($this->checker->hasOverlap($user, $target));
    }

    #[Test]
    public function it_detects_no_overlap_when_workshops_share_only_a_boundary_moment(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $existing */
        $existing = Workshop::factory()->create([
            'starts_at' => '2030-06-01 09:00:00',
            'ends_at' => '2030-06-01 11:00:00',
        ]);
        WorkshopRegistration::create([
            'user_id' => $user->id,
            'workshop_id' => $existing->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        /** @var Workshop $target */
        $target = Workshop::factory()->create([
            'starts_at' => '2030-06-01 11:00:00',
            'ends_at' => '2030-06-01 13:00:00',
        ]);

        $this::assertFalse($this->checker->hasOverlap($user, $target));
    }

    #[Test]
    public function it_returns_false_when_user_has_no_registrations(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $target */
        $target = Workshop::factory()->create([
            'starts_at' => '2030-06-01 09:00:00',
            'ends_at' => '2030-06-01 11:00:00',
        ]);

        $this::assertFalse($this->checker->hasOverlap($user, $target));
    }

    #[Test]
    public function it_ignores_waiting_registrations_when_checking_overlap(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $existing */
        $existing = Workshop::factory()->create([
            'starts_at' => '2030-06-01 09:00:00',
            'ends_at' => '2030-06-01 11:00:00',
        ]);
        WorkshopRegistration::create([
            'user_id' => $user->id,
            'workshop_id' => $existing->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 1,
        ]);

        /** @var Workshop $target */
        $target = Workshop::factory()->create([
            'starts_at' => '2030-06-01 10:00:00',
            'ends_at' => '2030-06-01 12:00:00',
        ]);

        $this::assertFalse($this->checker->hasOverlap($user, $target));
    }
}
