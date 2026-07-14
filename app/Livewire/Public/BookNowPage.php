<?php

namespace App\Livewire\Public;

use App\Models\Room;
use App\Models\Setting;
use App\Services\BookingSubmissionService;
use App\Support\HotelChannels;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class BookNowPage extends Component
{
    public int $step = 1;

    public $room_id = null;

    public string $checkin = '';

    public string $checkout = '';

    public int $adults = 2;

    public int $children = 0;

    public int $rooms = 1;

    public string $names = '';

    public string $phone = '';

    public string $email = '';

    public string $message = '';

    public string $confirm_via = 'email';

    public bool $agree_terms = false;

    public bool $submitted = false;

    public ?int $submittedBookingId = null;

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
    }

    public function updatedRoomId(): void
    {
        $this->resetValidation('room_id');
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
                'rooms' => $this->rooms,
                'names' => $this->names,
                'phone' => $this->phone,
                'email' => $this->email,
                'message' => trim($this->message."\nConfirmation preference: ".$this->confirm_via),
            ]);

            $result = $bookings->submit($request);
            $this->submittedBookingId = $result['booking']->id;
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

    protected function validateStay(): void
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'nullable|integer|min:0|max:20',
            'rooms' => 'required|integer|min:1|max:10',
        ]);

        $room = Room::find($this->room_id);
        if ($room && $room->status !== 'Active') {
            throw ValidationException::withMessages([
                'room_id' => site_trans('booking.wizard_room_unavailable'),
            ]);
        }

        if ($room && $room->max_occupancy) {
            $guests = (int) $this->adults + (int) $this->children;
            $capacity = (int) $room->max_occupancy * max(1, (int) $this->rooms);
            if ($guests > $capacity) {
                throw ValidationException::withMessages([
                    'adults' => site_trans('booking.wizard_capacity_exceeded', ['max' => $capacity]),
                ]);
            }
        }
    }

    protected function validateGuest(): void
    {
        $this->validate([
            'names' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
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
            $checkin = new DateTime($this->checkin);
            $checkout = new DateTime($this->checkout);
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

        $nightly = $room->nightlyRateForGuests((int) $this->adults, (int) $this->children);

        return $nightly * $nights * max(1, (int) $this->rooms);
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
            'names' => $this->names,
            'room' => $room?->title ?? '',
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'adults' => (string) $this->adults,
            'children' => (string) $this->children,
            'ref' => $bookingId ? '#'.$bookingId : '',
        ]);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }

    public function render()
    {
        $setting = Setting::first();
        $rooms = Room::query()
            ->where('status', 'Active')
            ->orderBy('title')
            ->get();

        $channels = HotelChannels::all();
        $publicPhone = hotel_public_phone($setting);
        $publicEmail = $channels['public_email'] ?? '';
        $hasWhatsapp = filled(preg_replace('/\D+/', '', (string) ($channels['whatsapp_e164'] ?? '')));

        return view('frontend.book-now', [
            'setting' => $setting,
            'rooms' => $rooms,
            'selectedRoom' => $this->selectedRoom(),
            'nights' => $this->nights(),
            'estimatedTotal' => $this->estimatedTotal(),
            'publicPhone' => $publicPhone,
            'publicEmail' => $publicEmail,
            'hasWhatsapp' => $hasWhatsapp,
        ]);
    }
}
