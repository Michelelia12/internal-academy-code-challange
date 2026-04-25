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

        // 7 empty upcoming workshops with random capacity
        Workshop::factory()->count(7)->create();

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
