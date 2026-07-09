<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdateGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $statusLabel,
        public string $adminReply
    ) {
    }

    public function build(): self
    {
        $itemName = $this->resolveItemName();
        $subject = 'Your reservation is '.strtolower($this->statusLabel).' — '.$itemName;

        return $this->subject($subject)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.booking-status-update-guest', [
                'hotelName' => BookingSubmissionService::hotelName(),
                'itemName' => $itemName,
            ]);
    }

    private function resolveItemName(): string
    {
        if ($this->booking->reservation_type === 'facility' && $this->booking->facility) {
            return $this->booking->facility->title;
        }
        if ($this->booking->reservation_type === 'tour_activity' && $this->booking->tourActivity) {
            return $this->booking->tourActivity->title;
        }
        if ($this->booking->room) {
            return $this->booking->room->title;
        }

        return 'your reservation';
    }
}
