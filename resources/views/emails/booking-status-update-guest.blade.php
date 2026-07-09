<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation update</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #333; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 20px;">Hello, {{ $booking->names }}</h1>
    <p>Your reservation for <strong>{{ $itemName }}</strong> at {{ $hotelName }} has been <strong>{{ strtolower($statusLabel) }}</strong>.</p>

    <p style="margin: 0 0 8px;"><strong>Message from the hotel</strong></p>
    <p style="margin: 0 0 24px; white-space: pre-wrap;">{{ $adminReply }}</p>

    @include('emails.partials.booking-details', ['booking' => $booking])

    <p style="font-size: 14px; color: #666;">If you have questions, reply to this email or contact the hotel directly.</p>
</body>
</html>
