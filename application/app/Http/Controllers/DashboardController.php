<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'workshops' => Workshop::query()
                ->where('starts_at', '>', now())
                ->withCount(['registrations as confirmed_registrations_count' => fn (Builder $query): Builder => $query->where('status', RegistrationStatus::Confirmed->value)])
                ->get()
                ->sortBy('starts_at')
                ->values(),
        ]);
    }
}
