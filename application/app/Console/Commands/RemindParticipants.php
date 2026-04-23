<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RegistrationStatus;
use App\Mail\WorkshopReminder;
use App\Models\WorkshopRegistration;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class RemindParticipants extends Command
{
    protected $signature = 'academy:remind';

    protected $description = 'Send reminder emails to participants of tomorrow\'s workshops';

    public function handle(): int
    {
        $workshopRegistrations = WorkshopRegistration::query()
            ->where('status', RegistrationStatus::Confirmed->value)
            ->whereHas('workshop', function (Builder $query): void {
                $query->where('starts_at', '>=', now()->addDay()->startOfDay())
                    ->where('starts_at', '<', now()->addDay()->endOfDay());
            })
            ->with(['user', 'workshop'])
            ->get();

        foreach ($workshopRegistrations as $workshopRegistration) {
            Mail::to($workshopRegistration->user)->send(
                new WorkshopReminder($workshopRegistration->user, $workshopRegistration->workshop)
            );
        }

        return self::SUCCESS;
    }
}
