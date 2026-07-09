<?php

namespace App\Services;

use App\Mail\BookingSubmittedAdminMail;
use App\Mail\BookingSubmittedGuestMail;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Setting;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingSubmissionService
{
    /**
     * @return array{booking: Booking, admin_sent: bool, guest_sent: bool}
     */
    public function submit(Request $request): array
    {
        $booking = $this->createBooking($request);
        $booking->load(['room', 'facility', 'tourActivity']);

        $adminSent = SiteNotificationMail::sendToTeam(new BookingSubmittedAdminMail($booking));
        $guestSent = SiteNotificationMail::sendToGuest($booking->email, new BookingSubmittedGuestMail($booking));

        return [
            'booking' => $booking,
            'admin_sent' => $adminSent,
            'guest_sent' => $guestSent,
        ];
    }

    public function createBooking(Request $request): Booking
    {
        $isFacility = $request->filled('facility_id');
        $isTourActivity = $request->filled('tour_activity_id');

        $rules = [
            'names' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:1000',
        ];

        if ($isFacility) {
            $rules['facility_id'] = 'required|exists:facilities,id';
            $rules['reservation_date'] = 'required|date|after_or_equal:today';
            $rules['guests'] = 'required|integer|min:1';
        } else {
            $rules['checkin'] = 'required|date|after_or_equal:today';
            $rules['checkout'] = 'required|date|after:checkin';
            $rules['adults'] = 'required|integer|min:1';
            $rules['children'] = 'nullable|integer|min:0';
            $rules['extra_beds'] = 'nullable|integer|min:0';
            $rules['rooms'] = 'nullable|integer|min:1';

            if ($isTourActivity) {
                $rules['tour_activity_id'] = 'required|exists:tour_activities,id';
            } else {
                $rules['room_id'] = 'required|exists:rooms,id';
            }
        }

        $request->validate($rules);

        $booking = new Booking();
        $booking->names = $request->input('names');
        $booking->email = $request->input('email');
        $booking->phone = $request->input('phone');
        $booking->message = $request->input('message');

        if (! $isFacility && ! $isTourActivity && $request->filled('extra_beds') && (int) $request->input('extra_beds') > 0) {
            $booking->message = trim(($booking->message ?? '')."\nExtra beds requested: ".(int) $request->input('extra_beds'));
        }

        if ($isFacility) {
            $booking->checkin_date = $request->input('reservation_date');
            $booking->checkout_date = $request->input('reservation_date');
            $booking->adults = $request->input('guests');
            $booking->children = 0;
        } else {
            $booking->checkin_date = $request->input('checkin');
            $booking->checkout_date = $request->input('checkout');
            $booking->adults = $request->input('adults');
            $booking->children = $request->input('children') ?? 0;
            $booking->rooms = $request->input('rooms') ?? 1;
        }

        $booking->status = 'pending';
        $booking->booking_type = 'online';
        $booking->payment_status = 'pending';
        $booking->paid_amount = 0;

        if (auth()->check()) {
            $booking->user_id = auth()->id();
        }

        if ($isTourActivity) {
            $booking->tour_activity_id = $request->input('tour_activity_id');
            $booking->reservation_type = 'tour_activity';
            $booking->room_id = null;
            $booking->facility_id = null;
            $booking->total_amount = 0;
            $booking->balance_amount = 0;
        } elseif ($isFacility) {
            $booking->facility_id = $request->input('facility_id');
            $booking->reservation_type = 'facility';
            $booking->room_id = null;
            $booking->tour_activity_id = null;
            $booking->total_amount = 0;
            $booking->balance_amount = 0;
        } else {
            $booking->room_id = $request->input('room_id');
            $booking->reservation_type = 'room';
            $booking->facility_id = null;
            $booking->tour_activity_id = null;

            $room = Room::findOrFail($request->input('room_id'));
            $checkin = new DateTime($request->input('checkin'));
            $checkout = new DateTime($request->input('checkout'));
            $nights = max(0, $checkin->diff($checkout)->days);
            $adults = (int) $request->input('adults');
            $children = (int) ($request->input('children') ?? 0);
            $extraBeds = (int) ($request->input('extra_beds') ?? 0);
            $roomCount = max(1, (int) ($request->input('rooms') ?? 1));

            $maxGuestsPerRoom = (int) ($room->max_occupancy ?? 0);
            $totalGuests = $adults + $children;
            if ($maxGuestsPerRoom > 0 && $totalGuests > ($maxGuestsPerRoom * $roomCount)) {
                $suggestedRooms = (int) ceil($totalGuests / $maxGuestsPerRoom);

                throw ValidationException::withMessages([
                    'rooms' => "Your selected number of rooms can't accommodate {$totalGuests} guests. Please set “Number of Rooms” to at least {$suggestedRooms}.",
                ]);
            }

            $nightly = $room->nightlyRateForGuests($adults, $children, $extraBeds);
            $booking->total_amount = (int) round($nightly * $nights * $roomCount);
            $booking->balance_amount = $booking->total_amount;
        }

        $booking->save();

        return $booking;
    }

    public static function hotelName(): string
    {
        $setting = Setting::first();
        $name = trim((string) ($setting?->company ?? ''));

        return $name !== '' ? $name : (string) config('mail.from.name', config('app.name'));
    }

    public static function adminSubject(Booking $booking): string
    {
        $typeLabel = match ($booking->reservation_type ?? 'room') {
            'facility' => 'Facility booking',
            'tour_activity' => 'Activity booking',
            default => 'Room booking',
        };

        return $typeLabel.' — '.$booking->names.' ('.$booking->id.')';
    }

    public static function guestSubject(): string
    {
        return 'We received your reservation — '.self::hotelName();
    }
}
