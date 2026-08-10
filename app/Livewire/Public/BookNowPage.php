<?php

namespace App\Livewire\Public;

use App\Models\Room;
use App\Models\Setting;
use App\Services\BookingSubmissionService;
use App\Services\RoomAvailabilityService;
use App\Support\BookingStayReasons;
use App\Support\HotelChannels;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class BookNowPage extends Component
{
    /** @var int */
    public $step = 1;

    /** @var int|string|null */
    public $room_id = null;

    /** @var string|null */
    public $checkin = '';

    /** @var string|null */
    public $checkout = '';

    /** @var string */
    public $party_type = 'individual';

    /** @var int|string|null */
    public $adults = 2;

    /** @var int|string|null */
    public $children = 0;

    /** @var int|string|null */
    public $room_count = 1;

    /** @var string|null */
    public $names = '';

    /** @var string|null */
    public $phone = '';

    /** @var string|null */
    public $email = '';

    /** @var string|null */
    public $country = '';

    /** @var string|null */
    public $stay_reason = '';

    /** @var string|null */
    public $message = '';

    /** @var string */
    public $confirm_via = 'email';

    /** @var bool */
    public $agree_terms = false;

    /** @var bool */
    public $submitted = false;

    /** @var int|null */
    public $submittedBookingId = null;

    /** @var bool */
    public $guestEmailSent = false;

    public function mount(): void
    {
        if (hotel_book_now_is_external()) {
            $this->redirect(hotel_book_now_url(), navigate: false);

            return;
        }

        $this->checkin = now()->toDateString();
        $this->checkout = now()->addDay()->toDateString();

        $preselect = (int) request()->query('room', 0);
        if ($preselect > 0 && Room::query()->where('id', $preselect)->where('status', 'Active')->exists()) {
            $this->room_id = $preselect;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->names = trim((string) ($user->name ?? ''));
            $this->email = trim((string) ($user->email ?? ''));
        }

        $this->normalizeStayInput();
    }

    public function updated($property): void
    {
        if (in_array($property, ['room_id', 'checkin', 'checkout', 'adults', 'children', 'room_count', 'party_type'], true)) {
            $this->normalizeStayInput();
            $this->syncLimitFeedback();
        }

        if (in_array($property, ['names', 'phone', 'email', 'country', 'stay_reason', 'message'], true)) {
            $this->{$property} = (string) ($this->{$property} ?? '');
        }
    }

    public function goToStep(int $step): void
    {
        if ($this->submitted) {
            return;
        }

        if ($step < 1 || $step > 3) {
            return;
        }

        if ($step > $this->step) {
            if ($step >= 2) {
                $this->validateStay();
            }
            if ($step >= 3) {
                $this->validateGuest();
            }
        }

        $this->step = $step;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateStay();
            $this->step = 2;

            return;
        }

        if ($this->step === 2) {
            $this->validateGuest();
            $this->step = 3;
        }
    }

    public function previousStep(): void
    {
        if ($this->submitted) {
            return;
        }

        $this->step = max(1, $this->step - 1);
    }

    public function confirmBooking(BookingSubmissionService $bookings): void
    {
        $this->validateStay();
        $this->validateGuest();
        $this->validate([
            'confirm_via' => 'required|in:email,whatsapp',
            'agree_terms' => 'accepted',
        ], [
            'agree_terms.accepted' => site_trans('booking.wizard_terms_required'),
        ]);

        try {
            $request = Request::create('/booking', 'POST', [
                'room_id' => $this->room_id,
                'checkin' => $this->checkin,
                'checkout' => $this->checkout,
                'adults' => $this->adults,
                'children' => $this->children,
                'rooms' => $this->room_count,
                'names' => $this->names,
                'phone' => $this->phone,
                'email' => $this->email,
                'country' => $this->country,
                'stay_reason' => $this->stay_reason,
                'party_type' => $this->party_type,
                'message' => trim((string) $this->message."\nConfirmation preference: ".$this->confirm_via),
            ]);

            $result = $bookings->submit($request);
            $this->submittedBookingId = $result['booking']->id;
            $this->guestEmailSent = (bool) ($result['guest_sent'] ?? false);
            $this->submitted = true;
            $this->step = 3;

            if ($this->confirm_via === 'whatsapp') {
                $whatsappUrl = $this->whatsappDeepLink($result['booking']->id);
                if ($whatsappUrl) {
                    $this->dispatch('open-url', url: $whatsappUrl);
                }
            }
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    protected function availability(): RoomAvailabilityService
    {
        return app(RoomAvailabilityService::class);
    }

    /**
     * @return array{inventory: int, available: int, held: int, max_occupancy: int, max_guests_for_request: int, dates_ready: bool}|null
     */
    protected function staySummary(): ?array
    {
        $room = $this->selectedRoom();
        if (! $room) {
            return null;
        }

        return $this->availability()->summary($room, (string) $this->checkin, (string) $this->checkout);
    }

    protected function normalizeStayInput(): void
    {
        $this->checkin = (string) ($this->checkin ?? '');
        $this->checkout = (string) ($this->checkout ?? '');
        $this->party_type = in_array($this->party_type, ['individual', 'group'], true)
            ? $this->party_type
            : 'individual';

        if ($this->room_id === '' || $this->room_id === null) {
            $this->room_id = null;
        } else {
            $this->room_id = (int) $this->room_id;
        }

        $isGroup = $this->party_type === 'group';
        $this->adults = max(1, (int) ($this->adults ?? ($isGroup ? 4 : 2)));
        $this->children = max(0, (int) ($this->children ?? 0));
        $this->room_count = max(1, (int) ($this->room_count ?? 1));

        // Soft ceilings only (party size), not room inventory — over-limit values keep
        // the typed number so we can show red labels / explanations instead of silently clamping.
        $maxAdults = $isGroup ? 120 : 20;
        $maxChildren = $isGroup ? 60 : 20;
        $this->adults = min($maxAdults, (int) $this->adults);
        $this->children = min($maxChildren, (int) $this->children);
        $this->room_count = min($isGroup ? 200 : 20, (int) $this->room_count);
    }

    /**
     * Surface limit explanations only when the guest exceeds inventory or occupancy.
     */
    protected function syncLimitFeedback(): void
    {
        $this->resetErrorBag(['adults', 'children', 'room_count']);

        $room = $this->selectedRoom();
        if (! $room) {
            return;
        }

        $summary = $this->availability()->summary($room, (string) $this->checkin, (string) $this->checkout);
        $available = max(0, (int) $summary['available']);
        $inventory = max(1, (int) $summary['inventory']);
        $maxOccupancy = max(1, (int) $summary['max_occupancy']);
        $roomCount = max(1, (int) $this->room_count);
        $guests = (int) $this->adults + (int) $this->children;
        $capacity = $maxOccupancy * $roomCount;

        if (($summary['dates_ready'] ?? false) && $available < 1) {
            $this->addError('room_count', site_trans('booking.wizard_dates_unavailable'));

            return;
        }

        if (($summary['dates_ready'] ?? false) && $roomCount > $available) {
            $this->addError('room_count', site_trans('booking.wizard_rooms_dates_inventory', [
                'max' => $available,
                'inventory' => $inventory,
            ]));
        }

        if ($guests > $capacity) {
            $needed = $this->availability()->minRoomsForGuests($room, (int) $this->adults, (int) $this->children);
            $this->addError('adults', site_trans('booking.wizard_capacity_need_rooms', [
                'max' => $capacity,
                'needed' => $needed,
                'per_room' => $maxOccupancy,
            ]));
        }
    }

    protected function validateStay(): void
    {
        $this->normalizeStayInput();
        $this->syncLimitFeedback();

        $summary = $this->staySummary();
        $available = max(0, (int) ($summary['available'] ?? 0));
        $isGroup = $this->party_type === 'group';
        $maxAdults = $isGroup ? 120 : 20;
        $maxChildren = $isGroup ? 60 : 20;
        $maxRoomsRule = max(1, $available > 0 ? $available : 1);

        $this->validate([
            'party_type' => 'required|in:individual,group',
            'room_id' => 'required|exists:rooms,id',
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'adults' => 'required|integer|min:1|max:'.$maxAdults,
            'children' => 'nullable|integer|min:0|max:'.$maxChildren,
            'room_count' => 'required|integer|min:1|max:'.$maxRoomsRule,
        ], [
            'room_count.max' => site_trans('booking.wizard_rooms_dates_inventory', [
                'max' => $available,
                'inventory' => (int) ($summary['inventory'] ?? $available),
            ]),
        ]);

        $room = Room::find($this->room_id);
        if ($room && $room->status !== 'Active') {
            throw ValidationException::withMessages([
                'room_id' => site_trans('booking.wizard_room_unavailable'),
            ]);
        }

        if ($room) {
            $this->availability()->assertRequestFits(
                $room,
                (string) $this->checkin,
                (string) $this->checkout,
                (int) $this->room_count,
                (int) $this->adults,
                (int) $this->children
            );
        }
    }

    protected function validateGuest(): void
    {
        $this->names = (string) ($this->names ?? '');
        $this->phone = (string) ($this->phone ?? '');
        $this->email = (string) ($this->email ?? '');
        $this->country = (string) ($this->country ?? '');
        $this->stay_reason = (string) ($this->stay_reason ?? '');
        $this->message = (string) ($this->message ?? '');

        $this->validate([
            'names' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:100',
            'stay_reason' => 'required|in:'.implode(',', array_keys(BookingStayReasons::options())),
            'message' => 'nullable|string|max:1000',
        ]);
    }

    public function selectedRoom(): ?Room
    {
        if (! $this->room_id) {
            return null;
        }

        return Room::query()->find($this->room_id);
    }

    public function nights(): int
    {
        try {
            $checkin = new DateTime((string) $this->checkin);
            $checkout = new DateTime((string) $this->checkout);
            $diff = (int) $checkin->diff($checkout)->days;

            return max(0, $diff);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function estimatedTotal(): float
    {
        $room = $this->selectedRoom();
        $nights = $this->nights();
        if (! $room || $nights < 1) {
            return 0.0;
        }

        $rooms = max(1, (int) $this->room_count);
        $adultsPerRoom = (int) max(1, ceil((int) $this->adults / $rooms));
        $childrenPerRoom = (int) max(0, ceil((int) $this->children / $rooms));
        $nightly = $room->nightlyRateForGuests($adultsPerRoom, $childrenPerRoom);

        return $nightly * $nights * $rooms;
    }

    protected function whatsappDeepLink(?int $bookingId = null): ?string
    {
        $channels = HotelChannels::all();
        $digits = preg_replace('/\D+/', '', (string) ($channels['whatsapp_e164'] ?? ''));
        if ($digits === '') {
            return null;
        }

        $room = $this->selectedRoom();
        $text = site_trans('booking.wizard_whatsapp_message', [
            'names' => (string) $this->names,
            'room' => $room?->title ?? '',
            'checkin' => (string) $this->checkin,
            'checkout' => (string) $this->checkout,
            'adults' => (string) $this->adults,
            'children' => (string) $this->children,
            'country' => (string) $this->country,
            'party' => (string) $this->party_type,
            'ref' => $bookingId ? '#'.$bookingId : '',
        ]);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }

    public function render()
    {
        $setting = Setting::first();
        $availability = $this->availability();
        $availableRooms = Room::query()
            ->where('status', 'Active')
            ->orderBy('title')
            ->get();

        $channels = HotelChannels::all();
        $publicPhone = hotel_public_phone($setting);
        $publicEmail = $channels['public_email'] ?? '';
        $hasWhatsapp = filled(preg_replace('/\D+/', '', (string) ($channels['whatsapp_e164'] ?? '')));
        $selectedRoom = $this->selectedRoom();
        $summary = $selectedRoom
            ? $availability->summary($selectedRoom, (string) $this->checkin, (string) $this->checkout)
            : null;

        $roomInventory = (int) ($summary['inventory'] ?? 1);
        $roomsAvailable = (int) ($summary['available'] ?? $roomInventory);
        $maxOccupancy = (int) ($summary['max_occupancy'] ?? 2);
        $maxGuests = $maxOccupancy * max(1, (int) $this->room_count);
        $minRoomsNeeded = $selectedRoom
            ? $availability->minRoomsForGuests($selectedRoom, (int) $this->adults, (int) $this->children)
            : 1;

        return view('frontend.book-now', [
            'setting' => $setting,
            'availableRooms' => $availableRooms,
            'selectedRoom' => $selectedRoom,
            'roomInventory' => $roomInventory,
            'roomsAvailable' => $roomsAvailable,
            'roomsHeld' => (int) ($summary['held'] ?? 0),
            'maxOccupancy' => $maxOccupancy,
            'maxGuests' => $maxGuests,
            'minRoomsNeeded' => $minRoomsNeeded,
            'datesReady' => (bool) ($summary['dates_ready'] ?? false),
            'stayReasons' => BookingStayReasons::options(),
            'nights' => $this->nights(),
            'estimatedTotal' => $this->estimatedTotal(),
            'publicPhone' => $publicPhone,
            'publicEmail' => $publicEmail,
            'hasWhatsapp' => $hasWhatsapp,
        ]);
    }
}
