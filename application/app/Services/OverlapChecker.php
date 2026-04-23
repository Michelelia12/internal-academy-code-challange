<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;

class OverlapChecker
{
    public function hasOverlap(User $user, Workshop $workshop): bool
    {
        return DB::table('registrations')
            ->join('workshops', 'registrations.workshop_id', '=', 'workshops.id')
            ->where('registrations.user_id', $user->id)
            ->where('registrations.status', RegistrationStatus::Confirmed->value)
            ->where('workshops.starts_at', '<', $workshop->ends_at)
            ->where('workshops.ends_at', '>', $workshop->starts_at)
            ->exists();
    }
}
