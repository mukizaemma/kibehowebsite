<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New booking</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 20px;">New booking request</h1>
    <p><strong>{{ $booking->names }}</strong> — {{ $booking->email }} — {{ $booking->phone }}</p>

    @include('emails.partials.booking-details', ['booking' => $booking])

    @if(filled($booking->message))
    <p style="margin: 0 0 8px;"><strong>Message</strong></p>
    <p style="margin: 0 0 24px; white-space: pre-wrap;">{{ $booking->message }}</p>
    @endif

    <p style="margin: 24px 0;">
        <a href="{{ route('dashboard') }}" style="display: inline-block; background: #0356b7; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold;">Open bookings in admin</a>
    </p>
    <p style="font-size: 13px; color: #666;">Confirm or reject from the admin dashboard and the guest will be notified by email.</p>
</body>
</html>
