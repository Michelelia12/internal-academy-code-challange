<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Workshop;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        Workshop::factory()->count(10)->create();

        // TODO(task-4.x): once Registration model exists, seed full workshops
        // with confirmed + waiting registrations here.
    }
}
