<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Workshop;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'workshops' => Workshop::query()
                ->where('starts_at', '>', now())
                ->get()
                ->sortBy('starts_at')
                ->values(),
        ]);
    }
}
