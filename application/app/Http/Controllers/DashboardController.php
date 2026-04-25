<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $workshops = Workshop::query()
            ->where('starts_at', '>', now())
            ->withCount(['registrations as confirmed_registrations_count' => fn (Builder $query): Builder => $query->where('status', RegistrationStatus::Confirmed->value)])
            ->get()
            ->sortBy('starts_at')
            ->values();

        $workshopIds = $workshops->pluck('id')->all();

        $userRegistrationsByWorkshop = WorkshopRegistration::query()
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn (WorkshopRegistration $reg): bool => in_array($reg->workshop_id, $workshopIds, true))
            ->keyBy('workshop_id');

        return Inertia::render('Dashboard', [
            'workshops' => $workshops->map(function (Workshop $workshop) use ($userRegistrationsByWorkshop): array {
                /** @var WorkshopRegistration|null $reg */
                $reg = $userRegistrationsByWorkshop->get($workshop->id);

                return array_merge($workshop->toArray(), [
                    'user_registration' => $reg instanceof WorkshopRegistration
                        ? ['status' => $reg->status->value]
                        : null,
                ]);
            }),
        ]);
    }
}
