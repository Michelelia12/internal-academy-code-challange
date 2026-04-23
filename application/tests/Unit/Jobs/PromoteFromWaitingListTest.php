<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\RegistrationStatus;
use App\Jobs\PromoteFromWaitingList;
use App\Mail\WaitingListPromotion;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(PromoteFromWaitingList::class)]
#[CoversClass(WaitingListPromotion::class)]
#[UsesClass(WorkshopRegistration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class PromoteFromWaitingListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_promotes_the_first_waiting_registration_to_confirmed(): void
    {
        Mail::fake();
        Queue::fake();

        /** @var User $other */
        $other = User::factory()->employee()->create();
        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 1]);

        WorkshopRegistration::create([
            'user_id' => $other->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);
        WorkshopRegistration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 1,
        ]);

        (new PromoteFromWaitingList($workshop))->handle();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed->value,
            'position' => null,
        ]);

        Mail::assertSent(WaitingListPromotion::class, function (WaitingListPromotion $mail) use ($user, $workshop): bool {
            return $mail->user->id === $user->id
                && $mail->workshop->id === $workshop->id;
        });
    }

    #[Test]
    public function it_promotes_the_lowest_position_first_when_multiple_are_waiting(): void
    {
        Mail::fake();
        Queue::fake();

        /** @var User $first */
        $first = User::factory()->employee()->create();
        /** @var User $second */
        $second = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 0]);

        WorkshopRegistration::create([
            'user_id' => $second->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 2,
        ]);
        WorkshopRegistration::create([
            'user_id' => $first->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 1,
        ]);

        (new PromoteFromWaitingList($workshop))->handle();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $first->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $second->id,
            'status' => RegistrationStatus::Waiting->value,
        ]);
    }

    #[Test]
    public function it_is_a_no_op_when_no_waiting_registrations(): void
    {
        Mail::fake();
        Queue::fake();

        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create();

        (new PromoteFromWaitingList($workshop))->handle();

        $this->assertDatabaseCount('registrations', 0);
        Mail::assertNothingSent();
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        Mail::fake();
        Queue::fake();

        /** @var User $user */
        $user = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create(['capacity' => 0]);

        WorkshopRegistration::create([
            'user_id' => $user->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 1,
        ]);

        (new PromoteFromWaitingList($workshop))->handle();
        (new PromoteFromWaitingList($workshop))->handle();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $user->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);
        Mail::assertSentCount(1);
    }

    #[Test]
    public function waiting_list_promotion_mailable_has_correct_subject_and_view(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->make();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->make();

        $mailable = new WaitingListPromotion($user, $workshop);

        $this::assertSame("You've been promoted from the waiting list!", $mailable->envelope()->subject);
        $this::assertSame('emails.waiting-list-promotion', $mailable->content()->view);
    }
}
