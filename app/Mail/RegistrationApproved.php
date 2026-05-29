<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationApproved extends Mailable
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
        return $this->subject('Thông báo: Đăng ký tham gia được chấp nhận')
            ->view('emails.registration_approved')
            ->with([
                'attendeeName' => $this->registration->attendee?->name ?? 'Người dùng',
                'eventName' => $event?->name ?? 'Sự kiện',
                'eventDate' => $event?->date_time?->format('d/m/Y H:i') ?? null,
                'eventLocation' => $event?->location ?? null,
            ]);
    }
}
