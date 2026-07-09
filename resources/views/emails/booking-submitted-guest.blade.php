<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking received</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 20px;">Thank you, {{ $booking->names }}</h1>
    <p>We have received your reservation request at <strong>{{ $hotelName }}</strong>. Our team will review it and get back to you shortly.</p>

    @include('emails.partials.booking-details', ['booking' => $booking])

    @if(filled($booking->message))
    <p style="margin: 0 0 8px;"><strong>Your message</strong></p>
    <p style="margin: 0 0 24px; white-space: pre-wrap;">{{ $booking->message }}</p>
    @endif

    <p style="font-size: 14px; color: #666;">If you need to reach us sooner, reply to this email or call the hotel.</p>
</body>
</html>
