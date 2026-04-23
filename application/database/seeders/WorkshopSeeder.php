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
    }
}
