<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\InvoiceSetting;
use App\Models\Room;
use App\Models\RoomClosure;
use App\Services\AuditLogger;
use App\Services\DeskAccess;
use App\Services\DeskPdf;
use App\Services\FolioService;
use App\Services\RoomHoldService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FrontOfficeDeskController extends Controller
{
    public function home()
    {
        $today = Carbon::today()->toDateString();
        $roomBookings = Booking::query()->where('reservation_type', 'room');

        $stats = [
            'arrivals' => (clone $roomBookings)->whereDate('checkin_date', $today)->whereIn('status', RoomHoldService::HOLDING)->count(),
            'in_house' => (clone $roomBookings)->where('status', 'checked_in')->count(),
            'departures' => (clone $roomBookings)->whereDate('checkout_date', $today)->whereIn('status', ['checked_in', 'reserved', 'confirmed', 'pending'])->count(),
            'held_tonight' => (clone $roomBookings)->whereIn('status', RoomHoldService::HOLDING)->whereDate('checkin_date', '<=', $today)->whereDate('checkout_date', '>', $today)->count(),
        ];

        return view('desk.home', compact('stats'));
    }

    public function reservations()
    {
        $reservations = Booking::query()
            ->where('reservation_type', 'room')
            ->with(['room', 'assignedRoom'])
            ->latest('id')
            ->paginate(30);

        return view('desk.reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $rooms = $this->rooms();
        $quote = $this->previewQuote($request);

        return view('desk.reservations.form', [
            'rooms' => $rooms,
            'quote' => $quote,
            'booking' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedStay($request);
        $booking = app(RoomHoldService::class)->place($data + [
            'booking_type' => 'walkin',
            'status' => 'reserved',
            'user_id' => null,
        ]);

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Reservation saved.');
    }

    public function show(Booking $booking)
    {
        $this->roomStay($booking);
        $booking->load(['room', 'assignedRoom', 'payments', 'charges' => fn ($q) => $q->whereNull('removed_at')]);

        return view('desk.reservations.show', compact('booking'));
    }

    public function edit(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $rooms = $this->rooms();
        $quote = $this->previewQuote($request, $booking);

        return view('desk.reservations.form', compact('rooms', 'quote', 'booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $data = $this->validatedStay($request);
        $booking = app(RoomHoldService::class)->revise($booking, $data + ['user_id' => $booking->user_id]);

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Reservation updated.');
    }

    public function checkInForm(Booking $booking)
    {
        $this->roomStay($booking);

        return view('desk.reservations.check-in', compact('booking'));
    }

    public function checkIn(Booking $booking)
    {
        $this->roomStay($booking);
        if (! in_array($booking->status, ['reserved', 'pending', 'confirmed'], true)) {
            return redirect()->route('desk.reservations.show', $booking)->with('error', 'This reservation cannot be checked in.');
        }

        $booking->status = 'checked_in';
        $booking->checked_in_at = now();
        $booking->checked_in_by = auth()->id();
        $booking->save();

        if ($booking->assigned_room_id) {
            Room::query()->whereKey($booking->assigned_room_id)->update(['room_status' => 'occupied']);
        }

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Guest checked in.');
    }

    public function checkOutForm(Booking $booking)
    {
        $this->roomStay($booking);

        return view('desk.reservations.check-out', compact('booking'));
    }

    public function checkOut(Booking $booking)
    {
        $this->roomStay($booking);
        if ($booking->status !== 'checked_in') {
            return redirect()->route('desk.reservations.show', $booking)->with('error', 'Check the guest in before checking them out.');
        }

        app(RoomHoldService::class)->checkOut($booking);

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Guest checked out. Tomorrow night is free for a new hold.');
    }

    public function cancelForm(Booking $booking)
    {
        $this->roomStay($booking);

        return view('desk.reservations.confirm', [
            'booking' => $booking,
            'title' => 'Cancel reservation',
            'description' => 'Cancelling releases these nights for '.$booking->names.'.',
            'action' => route('desk.reservations.cancel.store', $booking),
            'submit' => 'Cancel reservation',
        ]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $this->mustConfirm($request);
        $booking->status = 'cancelled';
        $booking->save();

        app(AuditLogger::class)->write(
            'reservation_cancelled',
            Booking::class,
            $booking->id,
            'Cancelled the reservation for '.$booking->names.'.',
            ['guest' => $booking->names, 'amount' => (float) $booking->total_amount, 'currency' => $booking->currency]
        );

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Reservation cancelled.');
    }

    public function noShowForm(Booking $booking)
    {
        $this->roomStay($booking);

        return view('desk.reservations.confirm', [
            'booking' => $booking,
            'title' => 'Mark no-show',
            'description' => 'A no-show releases these nights for '.$booking->names.'.',
            'action' => route('desk.reservations.no-show.store', $booking),
            'submit' => 'Mark no-show',
        ]);
    }

    public function noShow(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $this->mustConfirm($request);
        $booking->status = 'no_show';
        $booking->save();

        app(AuditLogger::class)->write(
            'reservation_no_show',
            Booking::class,
            $booking->id,
            $booking->names.' was marked as a no-show.',
            ['guest' => $booking->names, 'amount' => (float) $booking->total_amount, 'currency' => $booking->currency]
        );

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Marked as no-show.');
    }

    public function receipt(Booking $booking)
    {
        $this->roomStay($booking);
        $booking->load(['room', 'payments', 'charges' => fn ($q) => $q->whereNull('removed_at')]);

        return view('desk.reservations.receipt', [
            'booking' => $booking,
            'invoice' => InvoiceSetting::current(),
        ]);
    }

    public function receiptPdf(Booking $booking)
    {
        $this->roomStay($booking);
        $booking->load(['room', 'payments', 'charges' => fn ($q) => $q->whereNull('removed_at')]);

        return app(DeskPdf::class)->download('desk.print.document', [
            'title' => $booking->reference ?: 'Stay',
            'heading' => InvoiceSetting::current()->heading ?: 'Invoice',
            'footer' => InvoiceSetting::current()->footer,
            'body' => view('desk.print.stay', ['booking' => $booking])->render(),
        ], ($booking->reference ?: 'stay').'.pdf');
    }

    public function reservationsPdf()
    {
        $reservations = Booking::query()
            ->where('reservation_type', 'room')
            ->with('room')
            ->latest('id')
            ->limit(200)
            ->get();

        return app(DeskPdf::class)->download('desk.print.document', [
            'title' => 'Room reservations',
            'heading' => 'Room reservations',
            'footer' => InvoiceSetting::current()->footer,
            'body' => view('desk.print.reservation-list', compact('reservations'))->render(),
        ], 'room-reservations.pdf');
    }

    public function storePayment(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,momo,card',
        ]);

        app(FolioService::class)->addPayment($booking, (float) $data['amount'], (string) $booking->currency, $data['method']);

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Payment saved.');
    }

    public function createCharge(Booking $booking)
    {
        $this->roomStay($booking);

        return view('desk.reservations.charge', compact('booking'));
    }

    public function storeCharge(Request $request, Booking $booking)
    {
        $this->roomStay($booking);
        $data = $request->validate([
            'description' => 'required|string|max:120',
            'amount' => 'required|numeric|min:0.01',
        ]);

        app(FolioService::class)->addCharge($booking, $data['description'], (float) $data['amount'], (string) $booking->currency, 'extra');

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Charge added.');
    }

    public function removeChargeForm(BookingCharge $charge)
    {
        $this->authorizeRemoval($charge->created_by);

        return view('desk.reservations.confirm', [
            'booking' => $charge->booking,
            'title' => 'Remove charge',
            'description' => 'Remove '.$charge->description.' ('.desk_money((float) $charge->amount, $charge->currency).').',
            'action' => route('desk.charges.remove.store', $charge),
            'submit' => 'Remove charge',
        ]);
    }

    public function removeCharge(Request $request, BookingCharge $charge)
    {
        $this->authorizeRemoval($charge->created_by);
        $this->mustConfirm($request);
        $booking = $charge->booking;
        app(FolioService::class)->removeCharge($charge);

        return redirect()->route('desk.reservations.show', $booking)->with('success', 'Charge removed.');
    }

    public function calendar(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::today();
        $days = collect(range(0, 13))->map(fn (int $offset) => $start->copy()->addDays($offset));
        $end = $days->last()->copy()->addDay();
        $rooms = Room::query()->where('status', 'Active')->orderBy('title')->get();
        $bookings = Booking::query()
            ->where('reservation_type', 'room')
            ->whereDate('checkin_date', '<', $end->toDateString())
            ->whereDate('checkout_date', '>', $start->toDateString())
            ->get()
            ->groupBy('room_id');
        $closures = RoomClosure::query()
            ->whereDate('night', '>=', $start->toDateString())
            ->whereDate('night', '<=', $days->last()->toDateString())
            ->get()
            ->groupBy(fn (RoomClosure $closure) => $closure->room_id.'|'.$closure->night->toDateString());

        return view('desk.calendar', compact('rooms', 'days', 'bookings', 'closures', 'start'));
    }

    public function inventory()
    {
        $rooms = Room::query()->with('closures')->orderBy('title')->get();

        return view('desk.inventory', compact('rooms'));
    }

    public function updateOverbook(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'overbook' => 'required|integer|min:0|max:20',
        ]);
        Room::query()->whereKey($data['room_id'])->update(['overbook' => $data['overbook']]);

        return redirect()->route('desk.inventory')->with('success', 'Overbook saved.');
    }

    public function storeClosure(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'night' => 'required|date',
            'note' => 'nullable|string|max:120',
        ]);

        RoomClosure::query()->updateOrCreate(
            ['room_id' => $data['room_id'], 'night' => $data['night']],
            ['note' => $data['note'] ?? null, 'created_by' => auth()->id()]
        );

        return redirect()->route('desk.inventory')->with('success', 'Closure saved.');
    }

    public function reports(Request $request)
    {
        $start = $request->input('start', Carbon::today()->startOfMonth()->toDateString());
        $end = $request->input('end', Carbon::today()->toDateString());
        $bookings = Booking::query()
            ->where('reservation_type', 'room')
            ->whereDate('checkin_date', '>=', $start)
            ->whereDate('checkin_date', '<=', $end)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        $totals = [];
        foreach ($bookings as $booking) {
            $currency = strtolower((string) ($booking->currency ?: 'usd')) === 'rwf' ? 'rwf' : 'usd';
            $totals[$currency] = ($totals[$currency] ?? 0) + (float) $booking->total_amount;
        }

        return view('desk.reports', compact('bookings', 'totals', 'start', 'end'));
    }

    public function rates()
    {
        $rooms = $this->rooms();

        return view('desk.rates', compact('rooms'));
    }

    public function updateRates(Request $request)
    {
        $data = $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($data['prices'] as $id => $price) {
            if ($price === null || $price === '') {
                continue;
            }
            Room::query()->whereKey($id)->update(['price' => $price]);
        }

        return redirect()->route('desk.rates')->with('success', 'Rates saved.');
    }

    public function invoiceSettings()
    {
        $invoice = InvoiceSetting::current();

        return view('desk.invoice-settings', compact('invoice'));
    }

    public function updateInvoiceSettings(Request $request)
    {
        $data = $request->validate([
            'heading' => 'nullable|string|max:120',
            'footer' => 'nullable|string|max:1000',
        ]);
        $invoice = InvoiceSetting::current();
        $invoice->fill($data);
        $invoice->save();

        return redirect()->route('desk.invoice-settings')->with('success', 'Invoice settings saved.');
    }

    protected function rooms()
    {
        return Room::query()->where('status', 'Active')->orderBy('title')->get();
    }

    protected function roomStay(Booking $booking): void
    {
        if (($booking->reservation_type ?? 'room') !== 'room') {
            abort(404);
        }
    }

    protected function validatedStay(Request $request): array
    {
        return $request->validate([
            'names' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'room_id' => 'required|exists:rooms,id',
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'rooms' => 'nullable|integer|min:1',
            'message' => 'nullable|string|max:1000',
        ], [
            'room_id.required' => 'Choose a suite before changing these dates.',
        ]);
    }

    protected function previewQuote(Request $request, ?Booking $booking = null): ?array
    {
        $roomId = $request->input('room_id', $booking?->room_id);
        $checkin = $request->input('checkin', optional($booking?->checkin_date)->toDateString());
        $checkout = $request->input('checkout', optional($booking?->checkout_date)->toDateString());
        $room = $roomId ? Room::query()->find($roomId) : null;
        if (! $room || ! $checkin || ! $checkout) {
            return null;
        }

        try {
            return app(RoomHoldService::class)->quote(
                $room,
                $checkin,
                $checkout,
                (int) $request->input('adults', $booking?->adults ?? 1),
                (int) $request->input('children', $booking?->children ?? 0),
                max(1, (int) $request->input('rooms', $booking?->rooms ?? 1))
            );
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mustConfirm(Request $request): void
    {
        $request->validate([
            'confirm' => 'accepted',
        ], [
            'confirm.accepted' => 'Confirm the cancellation before it can be saved.',
        ]);
    }

    protected function authorizeRemoval(?int $createdBy): void
    {
        $user = auth()->user();
        if (! app(DeskAccess::class)->canRemoveOwned($user, $createdBy)) {
            abort(403, 'Only the person who added this, or a super admin, can remove it.');
        }
    }
}
