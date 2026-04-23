<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\RegistrationStatus;
use App\Mail\WaitingListPromotion;
use App\Models\Registration;
use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PromoteFromWaitingList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Workshop $workshop) {}

    public function handle(): void
    {
        /** @var Registration|null $registration */
        $registration = Registration::where('workshop_id', $this->workshop->id)
            ->where('status', RegistrationStatus::Waiting->value)
            ->get()
            ->sortBy('position')
            ->first();

        if ($registration === null) {
            return;
        }

        $registration->update([
            'status' => RegistrationStatus::Confirmed,
            'position' => null,
        ]);

        Mail::to($registration->user)->send(new WaitingListPromotion($registration->user, $this->workshop));
    }
}
