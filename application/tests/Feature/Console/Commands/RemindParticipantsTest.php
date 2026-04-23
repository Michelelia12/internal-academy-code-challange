<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\RemindParticipants;
use App\Enums\RegistrationStatus;
use App\Mail\WorkshopReminder;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RemindParticipants::class)]
#[CoversClass(WorkshopReminder::class)]
#[UsesClass(Registration::class)]
#[UsesClass(RegistrationStatus::class)]
#[UsesClass(User::class)]
#[UsesClass(Workshop::class)]
class RemindParticipantsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_sends_reminders_for_confirmed_registrations_tomorrow(): void
    {
        Mail::fake();

        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'starts_at' => now()->addDay()->startOfDay()->addHours(10),
            'ends_at' => now()->addDay()->startOfDay()->addHours(12),
        ]);
        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        $this->artisan('academy:remind');

        Mail::assertSent(WorkshopReminder::class, function (WorkshopReminder $mail) use ($employee): bool {
            return $mail->user->id === $employee->id;
        });
    }

    #[Test]
    public function it_sends_no_reminders_for_past_workshops(): void
    {
        Mail::fake();

        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'starts_at' => now()->subDay()->startOfDay()->addHours(10),
            'ends_at' => now()->subDay()->startOfDay()->addHours(12),
        ]);
        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        $this->artisan('academy:remind');

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_no_reminders_for_workshops_more_than_one_day_away(): void
    {
        Mail::fake();

        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'starts_at' => now()->addDays(2)->startOfDay()->addHours(10),
            'ends_at' => now()->addDays(2)->startOfDay()->addHours(12),
        ]);
        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        $this->artisan('academy:remind');

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_no_reminders_for_waiting_registrations(): void
    {
        Mail::fake();

        /** @var User $employee */
        $employee = User::factory()->employee()->create();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'starts_at' => now()->addDay()->startOfDay()->addHours(10),
            'ends_at' => now()->addDay()->startOfDay()->addHours(12),
        ]);
        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting,
            'position' => 1,
        ]);

        $this->artisan('academy:remind');

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_a_reminder_to_every_confirmed_participant(): void
    {
        Mail::fake();

        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'starts_at' => now()->addDay()->startOfDay()->addHours(10),
            'ends_at' => now()->addDay()->startOfDay()->addHours(12),
            'capacity' => 3,
        ]);
        /** @var User $u1 */
        $u1 = User::factory()->employee()->create();
        /** @var User $u2 */
        $u2 = User::factory()->employee()->create();
        /** @var User $u3 */
        $u3 = User::factory()->employee()->create();

        foreach ([$u1, $u2, $u3] as $user) {
            Registration::create([
                'user_id' => $user->id,
                'workshop_id' => $workshop->id,
                'status' => RegistrationStatus::Confirmed,
                'position' => null,
            ]);
        }

        $this->artisan('academy:remind');

        Mail::assertSentCount(3);
        Mail::assertSent(WorkshopReminder::class, fn (WorkshopReminder $m) => $m->user->id === $u1->id);
        Mail::assertSent(WorkshopReminder::class, fn (WorkshopReminder $m) => $m->user->id === $u2->id);
        Mail::assertSent(WorkshopReminder::class, fn (WorkshopReminder $m) => $m->user->id === $u3->id);
    }

    #[Test]
    public function workshop_reminder_mailable_has_correct_subject_and_view(): void
    {
        /** @var User $user */
        $user = User::factory()->employee()->make();
        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->make(['title' => 'Laravel Deep Dive']);

        $mailable = new WorkshopReminder($user, $workshop);

        $this::assertSame('Reminder: Laravel Deep Dive is tomorrow!', $mailable->envelope()->subject);
        $this::assertSame('emails.workshop-reminder', $mailable->content()->view);
    }
}
