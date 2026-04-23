<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    public function index(): Response
    {
        /** @var Workshop|null $mostPopular */
        $mostPopular = Workshop::withCount([
            'registrations as confirmed_count' => function (Builder $query): void {
                $query->where('status', RegistrationStatus::Confirmed->value);
            },
        ])
            ->get()
            ->sortByDesc('confirmed_count')
            ->first();

        $totalCount = DB::table('registrations')
            ->where('status', RegistrationStatus::Confirmed->value)
            ->count();

        return Inertia::render('Admin/Statistics', [
            'most_popular' => $mostPopular,
            'total_count' => $totalCount,
        ]);
    }
}
