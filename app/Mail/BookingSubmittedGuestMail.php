<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingSubmittedGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function build(): self
    {
        $from = config('mail.from.address');

        return $this->subject(BookingSubmissionService::guestSubject())
            ->from($from, config('mail.from.name'))
            ->replyTo($from, config('mail.from.name'))
            ->view('emails.booking-submitted-guest', [
                'hotelName' => BookingSubmissionService::hotelName(),
            ]);
    }
}
