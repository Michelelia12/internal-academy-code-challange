<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Events\RegistrationUpdated;
use App\Jobs\PromoteFromWaitingList;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\OverlapChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function __construct(private readonly OverlapChecker $overlapChecker) {}

    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $alreadyRegisteredQuery = WorkshopRegistration::query()->where('user_id', $user->id)
            ->where('workshop_id', $workshop->id);
        $alreadyRegistered = $alreadyRegisteredQuery->first();

        if ($alreadyRegistered !== null) {
            return redirect()->route('dashboard');
        }

        if ($this->overlapChecker->hasOverlap($user, $workshop)) {
            abort(422, 'This workshop overlaps with one you are already registered for.');
        }

        DB::transaction(function () use ($workshop, $user): void {
            $confirmedCount = WorkshopRegistration::where('workshop_id', $workshop->id)
                ->where('status', RegistrationStatus::Confirmed->value)
                ->toBase()
                ->count();

            if ($confirmedCount < $workshop->capacity) {
                WorkshopRegistration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Confirmed,
                    'position' => null,
                ]);
            } else {
                $position = WorkshopRegistration::where('workshop_id', $workshop->id)
                    ->where('status', RegistrationStatus::Waiting->value)
                    ->toBase()
                    ->count() + 1;

                WorkshopRegistration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Waiting,
                    'position' => $position,
                ]);
            }
        });

        $confirmedCount = DB::table('registrations')
            ->where('workshop_id', $workshop->id)
            ->where('status', RegistrationStatus::Confirmed->value)
            ->count();

        RegistrationUpdated::dispatch($workshop, $confirmedCount);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, Workshop $workshop): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        /** @var WorkshopRegistration|null $registration */
        $registration = WorkshopRegistration::query()
            ->where('user_id', $user->id)
            ->where('workshop_id', $workshop->id)
            ->first();

        if ($registration === null) {
            return redirect()->route('dashboard');
        }

        $wasConfirmed = $registration->status === RegistrationStatus::Confirmed;
        $registration->delete();

        if ($wasConfirmed) {
            PromoteFromWaitingList::dispatch($workshop);
        }

        $confirmedCount = DB::table('registrations')
            ->where('workshop_id', $workshop->id)
            ->where('status', RegistrationStatus::Confirmed->value)
            ->count();

        RegistrationUpdated::dispatch($workshop, $confirmedCount);

        return redirect()->route('dashboard');
    }
}
