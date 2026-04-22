<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $alreadyRegisteredQuery = Registration::query()->where('user_id', $user->id)
            ->where('workshop_id', $workshop->id);
        $alreadyRegistered = $alreadyRegisteredQuery->first();

        if (! is_null($alreadyRegistered)) {
            return redirect()->route('dashboard');
        }

        DB::transaction(function () use ($workshop, $user): void {
            $confirmedCount = Registration::where('workshop_id', $workshop->id)
                ->where('status', RegistrationStatus::Confirmed->value)
                ->toBase()
                ->count();

            if ($confirmedCount < $workshop->capacity) {
                Registration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Confirmed,
                    'position' => null,
                ]);
            } else {
                $position = Registration::where('workshop_id', $workshop->id)
                    ->where('status', RegistrationStatus::Waiting->value)
                    ->toBase()
                    ->count() + 1;

                Registration::create([
                    'user_id' => $user->id,
                    'workshop_id' => $workshop->id,
                    'status' => RegistrationStatus::Waiting,
                    'position' => $position,
                ]);
            }
        });

        return redirect()->route('dashboard');
    }
}
