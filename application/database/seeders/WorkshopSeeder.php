<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        // 3 full workshops (capacity 3) with confirmed registrations and a waiting list
        $fullWorkshops = Workshop::factory()->count(3)->create(['capacity' => 3]);

        foreach ($fullWorkshops as $workshop) {
            $confirmed = User::factory()->employee()->count(3)->create();
            foreach ($confirmed as $user) {
                WorkshopRegistration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Confirmed,
                    'position' => null,
                ]);
            }

            $waiting = User::factory()->employee()->count(2)->create();
            $position = 1;
            foreach ($waiting as $user) {
                WorkshopRegistration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Waiting,
                    'position' => $position++,
                ]);
            }
        }

        // [Waiting List QA] Charlie's Workshop — capacity 1, Charlie is the only confirmed participant.
        // Register as alice@academy.test → waiting list. Unsubscribe charlie → alice is promoted.
        $date = now()->addMonths(1)->toDateString();

        $charlieWorkshop = Workshop::create([
            'title' => 'Charlie\'s Workshop',
            'description' => 'Waiting List QA fixture — capacity 1, Charlie is the only confirmed participant. Register as alice to join the waiting list, then unsubscribe Charlie to trigger automatic promotion.',
            'starts_at' => "{$date} 10:00:00",
            'ends_at' => "{$date} 12:00:00",
            'capacity' => 1,
        ]);

        /** @var User $charlie */
        $charlie = User::where('email', 'charlie@academy.test')->firstOrFail();

        WorkshopRegistration::create([
            'user_id' => $charlie->id,
            'workshop_id' => $charlieWorkshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        // [Reminder QA] Tomorrow's Workshop — alice is confirmed. Run php artisan academy:remind after seeding.
        $tomorrow = now()->addDay()->toDateString();

        $reminderWorkshop = Workshop::create([
            'title' => '[Reminder QA] Tomorrow\'s Workshop',
            'description' => 'Reminder QA fixture — scheduled for tomorrow with Alice confirmed. Run php artisan academy:remind to send the reminder email.',
            'starts_at' => "{$tomorrow} 14:00:00",
            'ends_at' => "{$tomorrow} 16:00:00",
            'capacity' => 10,
        ]);

        /** @var User $alice */
        $alice = User::where('email', 'alice@academy.test')->firstOrFail();

        WorkshopRegistration::create([
            'user_id' => $alice->id,
            'workshop_id' => $reminderWorkshop->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        // [Overlap QA] Two workshops that intentionally overlap in time.
        // Session A 09:00–11:00, Session B 10:00–12:00 (1-hour overlap).
        // Charlie is confirmed for Session A — log in as charlie@academy.test
        // and try to register for Session B to trigger the 422.
        $overlapDate = now()->addMonths(2)->toDateString();

        /** @var Workshop $sessionA */
        $sessionA = Workshop::create([
            'title' => '[Overlap QA] Session A',
            'description' => 'Overlap QA fixture — Session A (09:00–11:00).',
            'starts_at' => "{$overlapDate} 09:00:00",
            'ends_at' => "{$overlapDate} 11:00:00",
            'capacity' => 10,
        ]);

        Workshop::create([
            'title' => '[Overlap QA] Session B',
            'description' => 'Overlap QA fixture — Session B (10:00–12:00). Overlaps with Session A.',
            'starts_at' => "{$overlapDate} 10:00:00",
            'ends_at' => "{$overlapDate} 12:00:00",
            'capacity' => 10,
        ]);

        /** @var User $charlie */
        $charlie = User::where('email', 'charlie@academy.test')->firstOrFail();

        WorkshopRegistration::create([
            'user_id' => $charlie->id,
            'workshop_id' => $sessionA->id,
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);
    }
}
