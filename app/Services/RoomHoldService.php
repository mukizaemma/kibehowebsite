<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\Room;
use App\Models\RoomClosure;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomHoldService
{
    /**
     * Statuses that still occupy a bedroom night.
     *
     * @return list<string>
     */
    public const HOLDING = ['pending', 'confirmed', 'reserved', 'checked_in'];

    public function availableUnits(Room $room, ?string $checkin, ?string $checkout, ?int $ignoreBookingId = null): int
    {
        $inventory = $this->inventory($room);
        if (! $this->validRange($checkin, $checkout)) {
            return $inventory;
        }

        $in = Carbon::parse($checkin)->startOfDay();
        $out = Carbon::parse($checkout)->startOfDay();
        if ($this->closureOnStay($room, $in, $out)) {
            return 0;
        }

        $held = $this->heldUnits($room, $in->toDateString(), $out->toDateString(), $ignoreBookingId);

        return max(0, $this->capacity($room) - $held);
    }

    public function heldUnits(Room $room, string $checkin, string $checkout, ?int $ignoreBookingId = null): int
    {
        return (int) $this->overlapping($room, $checkin, $checkout, $ignoreBookingId)
            ->get()
            ->sum(fn (Booking $booking) => max(1, (int) $booking->rooms));
    }

    public function place(array $input): Booking
    {
        return DB::transaction(function () use ($input) {
            $room = $this->lockRoom($input['room_id'] ?? null);
            $range = $this->rangeOrFail($input['checkin'] ?? null, $input['checkout'] ?? null);
            $units = max(1, (int) ($input['rooms'] ?? 1));
            $this->assertCanHold($room, $range['in'], $range['out'], $units, null);

            $booking = new Booking();
            $this->fillStay($booking, $room, $input, $range, $units);
            $booking->status = $input['status'] ?? 'reserved';
            $booking->save();
            $booking->reference = 'ST-'.str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT);
            $booking->save();

            return $booking->fresh(['room', 'assignedRoom']);
        });
    }

    public function revise(Booking $booking, array $input): Booking
    {
        return DB::transaction(function () use ($booking, $input) {
            $room = $this->lockRoom($input['room_id'] ?? $booking->room_id);
            $range = $this->rangeOrFail($input['checkin'] ?? null, $input['checkout'] ?? null);
            $units = max(1, (int) ($input['rooms'] ?? $booking->rooms ?? 1));
            $this->assertCanHold($room, $range['in'], $range['out'], $units, $booking->id);

            $this->fillStay($booking, $room, $input, $range, $units);
            $booking->save();

            return $booking->fresh(['room', 'assignedRoom']);
        });
    }

    public function checkOut(Booking $booking): Booking
    {
        $today = Carbon::today();
        $checkin = Carbon::parse($booking->checkin_date ?: $booking->checkin)->startOfDay();
        $checkout = Carbon::parse($booking->checkout_date ?: $booking->checkout)->startOfDay();
        if ($checkout->gt($today) && $today->gte($checkin)) {
            $booking->checkout_date = $today->toDateString();
            $booking->checkout = $today->toDateString();
        }
        $booking->status = 'checked_out';
        $booking->checked_out_at = now();
        $booking->checked_out_by = auth()->id();
        $booking->save();

        if ($booking->assigned_room_id) {
            Room::query()->whereKey($booking->assigned_room_id)->update(['room_status' => 'available']);
        }

        return $booking->fresh();
    }

    /**
     * @return array{nights: int, nightly: float, total: float, currency: string}
     */
    public function quote(Room $room, string $checkin, string $checkout, int $adults, int $children, int $units, int $extraBeds = 0): array
    {
        $in = Carbon::parse($checkin)->startOfDay();
        $out = Carbon::parse($checkout)->startOfDay();
        $nights = max(1, $in->diffInDays($out));
        $units = max(1, $units);
        $adultsPerRoom = (int) max(1, ceil(max(1, $adults) / $units));
        $childrenPerRoom = (int) max(0, ceil(max(0, $children) / $units));
        $nightly = $room->nightlyRateForGuests($adultsPerRoom, $childrenPerRoom, max(0, $extraBeds));
        $currency = $this->currency();
        $total = $nightly * $nights * $units;
        if ($currency === 'rwf') {
            $total = round($total);
            $nightly = round($nightly);
        } else {
            $total = round($total, 2);
            $nightly = round($nightly, 2);
        }

        return [
            'nights' => $nights,
            'nightly' => (float) $nightly,
            'total' => (float) $total,
            'currency' => $currency,
        ];
    }

    public function currency(): string
    {
        $setting = Setting::query()->first();

        return strtolower((string) ($setting?->price_currency ?? 'usd')) === 'rwf' ? 'rwf' : 'usd';
    }

    public function inventory(Room $room): int
    {
        return max(1, (int) ($room->number_of_rooms ?? 1));
    }

    public function capacity(Room $room): int
    {
        return $this->inventory($room) + max(0, (int) ($room->overbook ?? 0));
    }

    protected function lockRoom(mixed $roomId): Room
    {
        $room = Room::query()->whereKey((int) $roomId)->lockForUpdate()->first();
        if (! $room) {
            throw ValidationException::withMessages([
                'room_id' => 'Choose a suite before changing these dates.',
            ]);
        }

        return $room;
    }

    /**
     * @return array{in: Carbon, out: Carbon}
     */
    protected function rangeOrFail(mixed $checkin, mixed $checkout): array
    {
        if (! $this->validRange(is_string($checkin) ? $checkin : null, is_string($checkout) ? $checkout : null)) {
            throw ValidationException::withMessages([
                'checkout' => 'Check-out must be after check-in.',
            ]);
        }

        return [
            'in' => Carbon::parse($checkin)->startOfDay(),
            'out' => Carbon::parse($checkout)->startOfDay(),
        ];
    }

    protected function assertCanHold(Room $room, Carbon $checkin, Carbon $checkout, int $units, ?int $ignoreBookingId): void
    {
        $closure = $this->closureOnStay($room, $checkin, $checkout);
        if ($closure) {
            throw ValidationException::withMessages([
                'checkin' => 'This room is closed on '.$closure->night->toDateString().'. Choose a different night.',
            ]);
        }

        $held = (int) $this->overlapping($room, $checkin->toDateString(), $checkout->toDateString(), $ignoreBookingId)
            ->lockForUpdate()
            ->get()
            ->sum(fn (Booking $booking) => max(1, (int) $booking->rooms));

        if ($held + $units > $this->capacity($room)) {
            $name = $this->firstGuestName($room, $checkin->toDateString(), $checkout->toDateString(), $ignoreBookingId);

            throw ValidationException::withMessages([
                'room_id' => 'This room is already held by '.$name.' for these nights.',
            ]);
        }
    }

    protected function fillStay(Booking $booking, Room $room, array $input, array $range, int $units): void
    {
        $adults = max(1, (int) ($input['adults'] ?? $booking->adults ?? 1));
        $children = max(0, (int) ($input['children'] ?? $booking->children ?? 0));
        $extraBeds = max(0, (int) ($input['extra_beds'] ?? 0));
        $quote = $this->quote($room, $range['in']->toDateString(), $range['out']->toDateString(), $adults, $children, $units, $extraBeds);

        $message = trim((string) ($input['message'] ?? $booking->message ?? ''));
        if ($extraBeds > 0 && ! str_contains($message, 'Extra beds requested:')) {
            $message = trim($message."\nExtra beds requested: ".$extraBeds);
        }

        $extraCharges = $booking->exists
            ? (float) BookingCharge::query()->where('booking_id', $booking->id)->whereNull('removed_at')->sum('amount')
            : 0.0;
        $paid = (float) ($booking->paid_amount ?? 0);

        $booking->fill([
            'user_id' => array_key_exists('user_id', $input) ? $input['user_id'] : $booking->user_id,
            'created_by' => $booking->created_by ?: ($input['created_by'] ?? auth()->id()),
            'names' => $input['names'] ?? $booking->names,
            'phone' => $input['phone'] ?? $booking->phone,
            'email' => $input['email'] ?? $booking->email,
            'country' => $input['country'] ?? $booking->country,
            'stay_reason' => $input['stay_reason'] ?? $booking->stay_reason,
            'party_type' => $input['party_type'] ?? $booking->party_type ?? 'individual',
            'message' => $message !== '' ? $message : null,
            'checkin' => $range['in']->toDateString(),
            'checkout' => $range['out']->toDateString(),
            'checkin_date' => $range['in']->toDateString(),
            'checkout_date' => $range['out']->toDateString(),
            'adults' => $adults,
            'children' => $children,
            'rooms' => $units,
            'room_id' => $room->id,
            'assigned_room_id' => $room->id,
            'facility_id' => null,
            'tour_activity_id' => null,
            'reservation_type' => 'room',
            'booking_type' => $input['booking_type'] ?? $booking->booking_type ?? 'walkin',
            'currency' => $quote['currency'],
            'total_amount' => round($quote['total'] + $extraCharges, 2),
            'paid_amount' => $booking->exists ? $paid : 0,
            'payment_status' => $booking->exists ? $booking->payment_status : 'pending',
        ]);

        $booking->balance_amount = round((float) $booking->total_amount - (float) $booking->paid_amount, 2);
    }

    protected function overlapping(Room $room, string $checkin, string $checkout, ?int $ignoreBookingId)
    {
        $query = Booking::query()
            ->where('room_id', $room->id)
            ->whereIn('status', self::HOLDING)
            ->where(function ($q) {
                $q->whereNull('reservation_type')->orWhere('reservation_type', 'room');
            })
            ->whereDate('checkin_date', '<', $checkout)
            ->whereDate('checkout_date', '>', $checkin)
            ->whereNull('deleted_at')
            ->orderBy('id');

        if ($ignoreBookingId) {
            $query->where('id', '!=', $ignoreBookingId);
        }

        return $query;
    }

    protected function firstGuestName(Room $room, string $checkin, string $checkout, ?int $ignoreBookingId): string
    {
        $guest = $this->overlapping($room, $checkin, $checkout, $ignoreBookingId)->first();

        return $guest?->names ?: 'another guest';
    }

    protected function closureOnStay(Room $room, Carbon $checkin, Carbon $checkout): ?RoomClosure
    {
        return RoomClosure::query()
            ->where('room_id', $room->id)
            ->whereDate('night', '>=', $checkin->toDateString())
            ->whereDate('night', '<', $checkout->toDateString())
            ->orderBy('night')
            ->first();
    }

    protected function validRange(?string $checkin, ?string $checkout): bool
    {
        if (! filled($checkin) || ! filled($checkout)) {
            return false;
        }

        try {
            return Carbon::parse($checkout)->startOfDay()->gt(Carbon::parse($checkin)->startOfDay());
        } catch (\Throwable) {
            return false;
        }
    }
}
