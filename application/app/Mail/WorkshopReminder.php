<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkshopReminder extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Workshop $workshop,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Reminder: {$this->workshop->title} is tomorrow!");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.workshop-reminder');
    }
}
