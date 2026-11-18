<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\InvoiceSetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantBill;
use App\Models\RestaurantBillLine;
use App\Models\RestaurantPayment;
use App\Models\RestaurantReservation;
use App\Services\AuditLogger;
use App\Services\DeskAccess;
use App\Services\DeskPdf;
use App\Services\FolioService;
use App\Services\PosCart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosDeskController extends Controller
{
    public function home()
    {
        $open = RestaurantBill::query()->where('status', 'open')->count();
        $todayBills = RestaurantBill::query()->whereDate('created_at', today())->where('status', '!=', 'cancelled')->with('lines', 'payments')->get();
        $taken = [];
        foreach ($todayBills as $bill) {
            $currency = $bill->currency;
            $taken[$currency] = ($taken[$currency] ?? 0) + $bill->cashPaid();
        }

        return view('desk.pos.home', compact('open', 'taken'));
    }

    public function sale(Request $request)
    {
        $cart = PosCart::get();
        if ($request->filled('currency') && in_array($request->input('currency'), ['usd', 'rwf'], true) && $cart['lines'] === []) {
            $cart['currency'] = $request->input('currency');
            PosCart::put($cart);
        }

        $items = MenuItem::query()->where('is_active', true)->orderBy('name')->get();
        $categories = MenuCategory::query()->orderBy('sort_order')->orderBy('name')->get();
        $inHouse = $this->inHouse($cart['currency']);
        $reservations = RestaurantReservation::query()->where('status', 'booked')->latest('reserved_for')->limit(30)->get();
        $profile = Restaurant::deskProfile();
        $total = PosCart::total($cart);

        return view('desk.pos.sale', compact('cart', 'items', 'categories', 'inHouse', 'reservations', 'profile', 'total'));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);
        $item = MenuItem::query()->findOrFail($data['menu_item_id']);
        $cart = PosCart::get();
        $currency = strtolower((string) $item->currency) === 'rwf' ? 'rwf' : 'usd';
        if ($cart['lines'] !== [] && $cart['currency'] !== $currency) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'This sale is in '.strtoupper($cart['currency']).'. Start a new sale for '.strtoupper($currency).'.',
            ]);
        }
        $cart['currency'] = $currency;
        $cart['lines'][] = [
            'menu_item_id' => $item->id,
            'item_name' => $item->name,
            'unit_price' => (float) $item->price,
            'quantity' => (int) ($data['quantity'] ?? 1),
            'currency' => $currency,
        ];
        PosCart::put($cart);

        return redirect()->route('desk.pos.sale');
    }

    public function removeCartLine(Request $request)
    {
        $index = (int) $request->input('index');
        $cart = PosCart::get();
        if (isset($cart['lines'][$index])) {
            unset($cart['lines'][$index]);
            $cart['lines'] = array_values($cart['lines']);
        }
        PosCart::put($cart);

        return redirect()->route('desk.pos.sale');
    }

    public function storeSale(Request $request)
    {
        $cart = PosCart::get();
        if ($cart['lines'] === []) {
            throw ValidationException::withMessages([
                'lines' => 'Add at least one item to the cart.',
            ]);
        }

        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'pay_amount' => 'nullable|numeric|min:0',
            'method' => 'nullable|in:cash,momo,card',
            'booking_id' => 'nullable|exists:bookings,id',
            'restaurant_reservation_id' => 'nullable|exists:restaurant_reservations,id',
        ]);

        $profile = Restaurant::deskProfile();
        $total = PosCart::total($cart);
        $minimum = $cart['currency'] === 'rwf' ? (float) $profile->minimum_amount_local : (float) $profile->minimum_amount;
        if ($minimum > 0 && $total < $minimum) {
            throw ValidationException::withMessages([
                'lines' => 'Add items until the bill reaches '.desk_money($minimum, $cart['currency']).'.',
            ]);
        }
        if ($profile->requires_reservation && empty($data['restaurant_reservation_id'])) {
            throw ValidationException::withMessages([
                'restaurant_reservation_id' => 'Choose a restaurant reservation before saving this sale.',
            ]);
        }

        $pay = round((float) ($data['pay_amount'] ?? 0), 2);
        if ($pay > $total) {
            throw ValidationException::withMessages([
                'pay_amount' => 'The payment is higher than the bill.',
            ]);
        }

        $chargeRoom = $request->filled('booking_id');
        if ($chargeRoom) {
            $this->assertChargeToRoom();
        }
        if ($pay > 0 && ! auth()->user()->allowsDeskOperation('payments')) {
            abort(403, 'You do not have access to this action.');
        }

        $bill = RestaurantBill::query()->create([
            'reference' => 'TMP-'.Str::lower(Str::random(10)),
            'status' => 'open',
            'guest_name' => $data['guest_name'],
            'currency' => $cart['currency'],
            'restaurant_reservation_id' => $data['restaurant_reservation_id'] ?? null,
            'created_by' => auth()->id(),
        ]);
        $bill->reference = 'RB-'.str_pad((string) $bill->id, 5, '0', STR_PAD_LEFT);
        $bill->save();

        foreach ($cart['lines'] as $line) {
            RestaurantBillLine::query()->create([
                'restaurant_bill_id' => $bill->id,
                'menu_item_id' => $line['menu_item_id'],
                'item_name' => $line['item_name'],
                'unit_price' => $line['unit_price'],
                'quantity' => $line['quantity'],
                'currency' => $line['currency'],
                'created_by' => auth()->id(),
            ]);
        }

        if ($pay > 0) {
            RestaurantPayment::query()->create([
                'restaurant_bill_id' => $bill->id,
                'amount' => $pay,
                'currency' => $cart['currency'],
                'method' => $data['method'] ?? 'cash',
                'user_id' => auth()->id(),
            ]);
        }

        $bill->load('lines', 'payments');
        if ($chargeRoom && $bill->balance() > 0) {
            $booking = Booking::query()->findOrFail($data['booking_id']);
            app(FolioService::class)->chargeBillToRoom($bill, $booking);
        }

        PosCart::clear();

        return redirect()->route('desk.pos.bills.show', $bill)->with('success', 'Sale saved.');
    }

    public function clearSale()
    {
        PosCart::clear();

        return redirect()->route('desk.pos.sale');
    }

    public function bills()
    {
        $bills = RestaurantBill::query()->with('lines', 'payments')->latest('id')->paginate(30);

        return view('desk.pos.bills.index', compact('bills'));
    }

    public function showBill(RestaurantBill $bill)
    {
        $bill->load('lines.creator', 'payments', 'booking');
        $inHouse = $this->inHouse($bill->currency);

        return view('desk.pos.bills.show', compact('bill', 'inHouse'));
    }

    public function storeBillPayment(Request $request, RestaurantBill $bill)
    {
        $bill->load('lines', 'payments');
        if ($bill->status === 'cancelled') {
            return redirect()->route('desk.pos.bills.show', $bill)->with('error', 'This bill is cancelled.');
        }
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,momo,card',
        ]);
        if ((float) $data['amount'] > $bill->balance()) {
            throw ValidationException::withMessages([
                'amount' => 'The payment is higher than the balance.',
            ]);
        }

        RestaurantPayment::query()->create([
            'restaurant_bill_id' => $bill->id,
            'amount' => $data['amount'],
            'currency' => $bill->currency,
            'method' => $data['method'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('desk.pos.bills.show', $bill)->with('success', 'Payment saved.');
    }

    public function chargeRoom(Request $request, RestaurantBill $bill)
    {
        $this->assertChargeToRoom();
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);
        $bill->load('lines', 'payments');
        $booking = Booking::query()->findOrFail($data['booking_id']);
        app(FolioService::class)->chargeBillToRoom($bill, $booking);

        return redirect()->route('desk.pos.bills.show', $bill)->with('success', 'Charged to the room.');
    }

    public function cancelForm(RestaurantBill $bill)
    {
        return view('desk.pos.confirm', [
            'title' => 'Cancel bill',
            'description' => 'Cancelling '.$bill->reference.' removes any room charge for '.$bill->guest_name.'.',
            'action' => route('desk.pos.bills.cancel.store', $bill),
            'submit' => 'Cancel bill',
        ]);
    }

    public function cancel(Request $request, RestaurantBill $bill)
    {
        $this->mustConfirm($request);
        $bill->load('lines');
        $bill->status = 'cancelled';
        $bill->cancelled_at = now();
        $bill->save();

        $charges = BookingCharge::query()->where('restaurant_bill_id', $bill->id)->whereNull('removed_at')->get();
        foreach ($charges as $charge) {
            app(FolioService::class)->removeCharge($charge);
        }

        app(AuditLogger::class)->write(
            'restaurant_bill_cancelled',
            RestaurantBill::class,
            $bill->id,
            'Cancelled restaurant bill '.$bill->reference.' for '.$bill->guest_name.'.',
            [
                'guest' => $bill->guest_name,
                'reference' => $bill->reference,
                'amount' => $bill->total(),
                'currency' => $bill->currency,
            ]
        );

        return redirect()->route('desk.pos.bills.show', $bill)->with('success', 'Bill cancelled.');
    }

    public function removeLineForm(RestaurantBill $bill, RestaurantBillLine $line)
    {
        $this->lineBelongs($bill, $line);
        $this->authorizeRemoval($line->created_by);

        return view('desk.pos.confirm', [
            'title' => 'Remove line',
            'description' => 'Remove '.$line->item_name.' from '.$bill->reference.'.',
            'action' => route('desk.pos.bills.lines.remove.store', [$bill, $line]),
            'submit' => 'Remove line',
        ]);
    }

    public function removeLine(Request $request, RestaurantBill $bill, RestaurantBillLine $line)
    {
        $this->lineBelongs($bill, $line);
        $this->authorizeRemoval($line->created_by);
        $this->mustConfirm($request);
        $amount = (float) $line->unit_price * (int) $line->quantity;
        app(AuditLogger::class)->write(
            'restaurant_line_removed',
            RestaurantBillLine::class,
            $line->id,
            'Removed '.$line->item_name.' ('.desk_money($amount, $line->currency).') from '.$bill->reference.'.',
            [
                'guest' => $bill->guest_name,
                'item' => $line->item_name,
                'amount' => $amount,
                'currency' => $line->currency,
            ]
        );
        $line->delete();

        return redirect()->route('desk.pos.bills.show', $bill)->with('success', 'Line removed.');
    }

    public function billPdf(RestaurantBill $bill)
    {
        $bill->load('lines', 'payments', 'booking');

        return app(DeskPdf::class)->download('desk.print.document', [
            'title' => $bill->reference,
            'heading' => InvoiceSetting::current()->heading ?: 'Invoice',
            'footer' => InvoiceSetting::current()->footer,
            'body' => view('desk.print.restaurant', ['bill' => $bill])->render(),
        ], $bill->reference.'.pdf');
    }

    public function reservations()
    {
        $reservations = RestaurantReservation::query()->with(['bills.lines', 'bills.payments'])->latest('reserved_for')->paginate(30);

        return view('desk.pos.reservations', compact('reservations'));
    }

    public function storeReservation(Request $request)
    {
        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'party_size' => 'required|integer|min:1',
            'reserved_for' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);
        $data['created_by'] = auth()->id();
        $data['status'] = 'booked';
        RestaurantReservation::query()->create($data);

        return redirect()->route('desk.pos.reservations')->with('success', 'Reservation saved.');
    }

    public function reports()
    {
        $bills = RestaurantBill::query()->where('status', '!=', 'cancelled')->with('payments')->get();
        $cash = [];
        foreach ($bills as $bill) {
            $cash[$bill->currency] = ($cash[$bill->currency] ?? 0) + $bill->cashPaid();
        }
        $onRoom = [];
        $charges = BookingCharge::query()->where('source', 'restaurant')->whereNull('removed_at')->get();
        foreach ($charges as $charge) {
            $onRoom[$charge->currency] = ($onRoom[$charge->currency] ?? 0) + (float) $charge->amount;
        }

        return view('desk.pos.reports', compact('cash', 'onRoom'));
    }

    public function menu()
    {
        $categories = MenuCategory::query()->with('items')->orderBy('sort_order')->orderBy('name')->get();
        $items = MenuItem::query()->with('category')->orderBy('name')->get();

        return view('desk.pos.menu', compact('categories', 'items'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80']);
        MenuCategory::query()->create($data);

        return redirect()->route('desk.pos.menu')->with('success', 'Category saved.');
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:usd,rwf',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
        ]);
        MenuItem::query()->create($data + ['is_active' => true]);

        return redirect()->route('desk.pos.menu')->with('success', 'Item saved.');
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:usd,rwf',
            'is_active' => 'nullable|boolean',
        ]);
        $item->fill($data);
        $item->is_active = $request->boolean('is_active');
        $item->save();

        return redirect()->route('desk.pos.menu')->with('success', 'Item updated. Bills already saved keep their prices.');
    }

    public function deleteItemForm(MenuItem $item)
    {
        return view('desk.pos.confirm', [
            'title' => 'Delete menu item',
            'description' => 'Delete '.$item->name.'. Bills that already used it keep the saved line.',
            'action' => route('desk.pos.menu.items.delete', $item),
            'submit' => 'Delete item',
        ]);
    }

    public function deleteItem(Request $request, MenuItem $item)
    {
        $this->mustConfirm($request);
        $item->delete();

        return redirect()->route('desk.pos.menu')->with('success', 'Item deleted.');
    }

    public function settings()
    {
        $profile = Restaurant::deskProfile();

        return view('desk.pos.settings', compact('profile'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'requires_reservation' => 'nullable|boolean',
            'minimum_amount' => 'nullable|numeric|min:0',
            'minimum_amount_local' => 'nullable|numeric|min:0',
            'momo_code' => 'nullable|string|max:40',
            'momo_account_name' => 'nullable|string|max:120',
        ]);
        $profile = Restaurant::deskProfile();
        $profile->requires_reservation = $request->boolean('requires_reservation');
        $profile->minimum_amount = $data['minimum_amount'] ?? 0;
        $profile->minimum_amount_local = $data['minimum_amount_local'] ?? 0;
        $profile->momo_code = $data['momo_code'] ?? null;
        $profile->momo_account_name = $data['momo_account_name'] ?? null;
        $profile->save();

        return redirect()->route('desk.pos.settings')->with('success', 'Settings saved.');
    }

    protected function inHouse(?string $currency = null)
    {
        $stays = Booking::query()
            ->with('room')
            ->where('reservation_type', 'room')
            ->where('status', 'checked_in')
            ->whereNull('checked_out_at')
            ->orderBy('names')
            ->get();

        if ($currency === null) {
            return $stays;
        }

        $wanted = strtolower($currency) === 'rwf' ? 'rwf' : 'usd';

        return $stays
            ->filter(fn (Booking $stay) => (strtolower((string) $stay->currency) === 'rwf' ? 'rwf' : 'usd') === $wanted)
            ->values();
    }

    protected function assertChargeToRoom(): void
    {
        $user = auth()->user();
        if (! $user->hasDeskModule('front_office') || ! $user->hasDeskModule('pos')) {
            abort(403, 'Charge to room needs both front office and POS access.');
        }
    }

    protected function lineBelongs(RestaurantBill $bill, RestaurantBillLine $line): void
    {
        if ((int) $line->restaurant_bill_id !== (int) $bill->id) {
            abort(404);
        }
    }

    protected function authorizeRemoval(?int $createdBy): void
    {
        if (! app(DeskAccess::class)->canRemoveOwned(auth()->user(), $createdBy)) {
            abort(403, 'Only the person who added this, or a super admin, can remove it.');
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
}
