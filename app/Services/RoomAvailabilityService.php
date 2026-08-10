<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class RoomAvailabilityService
{
    /**
     * Booking statuses that still occupy inventory for a date range.
     *
     * @return list<string>
     */
    public static function holdingStatuses(): array
    {
        return ['pending', 'confirmed'];
    }

    public function inventory(Room $room): int
    {
        return max(1, (int) ($room->number_of_rooms ?? 1));
    }

    public function maxOccupancy(Room $room): int
    {
        return max(1, (int) ($room->max_occupancy ?? 1));
    }

    /**
     * Rooms of this type still free to request for [check-in, check-out).
     */
    public function availableCount(Room $room, ?string $checkin, ?string $checkout, ?int $ignoreBookingId = null): int
    {
        $inventory = $this->inventory($room);
        if (! $this->hasValidStayDates($checkin, $checkout)) {
            return $inventory;
        }

        $held = $this->heldRoomsCount($room, $checkin, $checkout, $ignoreBookingId);

        return max(0, $inventory - $held);
    }

    public function heldRoomsCount(Room $room, string $checkin, string $checkout, ?int $ignoreBookingId = null): int
    {
        if (! Schema::hasTable('bookings')) {
            return 0;
        }

        $query = Booking::query()
            ->where('room_id', $room->id)
            ->whereIn('status', self::holdingStatuses())
            ->where(function ($q) {
                $q->whereNull('reservation_type')->orWhere('reservation_type', 'room');
            })
            // Standard hotel nights: occupy check-in night through night before check-out.
            ->whereDate('checkin_date', '<', $checkout)
            ->whereDate('checkout_date', '>', $checkin);

        if ($ignoreBookingId) {
            $query->where('id', '!=', $ignoreBookingId);
        }

        return (int) $query->sum('rooms');
    }

    public function minRoomsForGuests(Room $room, int $adults, int $children): int
    {
        $guests = max(0, $adults) + max(0, $children);
        if ($guests < 1) {
            return 1;
        }

        return (int) max(1, (int) ceil($guests / $this->maxOccupancy($room)));
    }

    public function maxGuestsForRooms(Room $room, int $roomCount): int
    {
        return $this->maxOccupancy($room) * max(1, $roomCount);
    }

    /**
     * @return array{
     *   inventory: int,
     *   available: int,
     *   held: int,
     *   max_occupancy: int,
     *   max_guests_for_request: int,
     *   dates_ready: bool
     * }
     */
    public function summary(Room $room, ?string $checkin, ?string $checkout, ?int $ignoreBookingId = null): array
    {
        $inventory = $this->inventory($room);
        $datesReady = $this->hasValidStayDates($checkin, $checkout);
        $held = $datesReady ? $this->heldRoomsCount($room, (string) $checkin, (string) $checkout, $ignoreBookingId) : 0;
        $available = $datesReady ? max(0, $inventory - $held) : $inventory;

        return [
            'inventory' => $inventory,
            'available' => $available,
            'held' => $held,
            'max_occupancy' => $this->maxOccupancy($room),
            'max_guests_for_request' => $this->maxOccupancy($room) * max(0, $available),
            'dates_ready' => $datesReady,
        ];
    }

    public function assertRequestFits(Room $room, string $checkin, string $checkout, int $roomCount, int $adults, int $children, ?int $ignoreBookingId = null): void
    {
        $summary = $this->summary($room, $checkin, $checkout, $ignoreBookingId);
        $roomCount = max(1, $roomCount);

        if ($summary['available'] < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'room_count' => site_trans('booking.wizard_dates_unavailable'),
            ]);
        }

        if ($roomCount > $summary['available']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'room_count' => site_trans('booking.wizard_rooms_dates_inventory', [
                    'max' => $summary['available'],
                    'inventory' => $summary['inventory'],
                ]),
            ]);
        }

        $guests = max(0, $adults) + max(0, $children);
        $capacity = $this->maxGuestsForRooms($room, $roomCount);
        if ($guests > $capacity) {
            $needed = $this->minRoomsForGuests($room, $adults, $children);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'adults' => site_trans('booking.wizard_capacity_need_rooms', [
                    'max' => $capacity,
                    'needed' => $needed,
                    'per_room' => $summary['max_occupancy'],
                ]),
            ]);
        }
    }

    protected function hasValidStayDates(?string $checkin, ?string $checkout): bool
    {
        if (! filled($checkin) || ! filled($checkout)) {
            return false;
        }

        try {
            $in = Carbon::parse($checkin)->startOfDay();
            $out = Carbon::parse($checkout)->startOfDay();

            return $out->gt($in);
        } catch (\Throwable) {
            return false;
        }
    }
}
