<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;

    /**
     * Create a new message instance.
     */
    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $event = $this->registration->event;
        return $this->subject('Notification: Registration Not Approved')
            ->view('emails.registration_rejected')
            ->with([
                'attendeeName' => $this->registration->attendee?->name ?? 'User',
                'eventName' => $event?->name ?? 'Event',
            ]);
    }
}
