<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->is_admin);

        Inertia::share([
            'auth' => fn (): array => [
                'user' => request()->user()?->only('name', 'is_admin'),
            ],
        ]);
    }
}
