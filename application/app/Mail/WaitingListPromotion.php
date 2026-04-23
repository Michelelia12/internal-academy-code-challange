<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitingListPromotion extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Workshop $workshop,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "You've been promoted from the waiting list!");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.waiting-list-promotion');
    }
}
