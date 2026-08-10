<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingSubmittedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function build(): self
    {
        $mailable = $this->subject(BookingSubmissionService::adminSubject($this->booking))
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.booking-submitted-admin');

        if (filled($this->booking->email)) {
            $mailable->replyTo($this->booking->email, $this->booking->names ?: $this->booking->email);
        }

        return $mailable;
    }
}
