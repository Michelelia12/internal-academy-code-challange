<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@academy.test',
        ]);

        foreach (['Alice' => 'alice', 'Bob' => 'bob', 'Charlie' => 'charlie'] as $name => $handle) {
            User::factory()->employee()->create([
                'name' => $name,
                'email' => "{$handle}@academy.test",
            ]);
        }
    }
}
