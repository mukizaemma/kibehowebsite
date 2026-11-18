@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'New POS sale')
@section('content')
@php $canRoom = auth()->user()->hasDeskModule('front_office') && auth()->user()->hasDeskModule('pos'); @endphp
<div class="desk-pagehead">
    <div>
        <h1>New POS sale</h1>
        <p>Prices are copied onto the bill and stay there if the menu changes.</p>
    </div>
    <button class="btn btn-dark" type="submit" form="sale-form">Save sale</button>
</div>
<div class="desk-sale">
    <div class="desk-card">
        <form method="get" class="desk-form mb-3">
            <label for="currency">Currency</label>
            <select id="currency" name="currency" onchange="this.form.submit()" @disabled($cart['lines'] !== [])>
                <option value="usd" @selected($cart['currency'] === 'usd')>USD</option>
                <option value="rwf" @selected($cart['currency'] === 'rwf')>RWF</option>
            </select>
        </form>
        @forelse($categories as $category)
            <h2 class="h6 mt-3">{{ $category->name }}</h2>
            @foreach($items->where('menu_category_id', $category->id) as $item)
                @if($item->currency === $cart['currency'] || $cart['lines'] === [])
                    <form method="post" action="{{ route('desk.pos.sale.items') }}" class="d-flex justify-content-between align-items-center gap-2 py-1">
                        @csrf
                        <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                        <span>{{ $item->name }}</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="num">{{ desk_money((float) $item->price, $item->currency) }}</span>
                            <button class="btn btn-outline-dark btn-sm" type="submit">Add</button>
                        </span>
                    </form>
                @endif
            @endforeach
        @endforelse
        @foreach($items->whereNull('menu_category_id') as $item)
            <form method="post" action="{{ route('desk.pos.sale.items') }}" class="d-flex justify-content-between align-items-center gap-2 py-1">
                @csrf
                <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                <span>{{ $item->name }}</span>
                <button class="btn btn-outline-dark btn-sm" type="submit">Add</button>
            </form>
        @endforeach
        @if($items->isEmpty())
            <p class="desk-muted">No menu items yet.</p>
        @endif
    </div>
    <form id="sale-form" class="desk-card desk-form" method="post" action="{{ route('desk.pos.sale.store') }}">
        @csrf
        <h2 class="h6">Cart</h2>
        @forelse($cart['lines'] as $index => $line)
            <div class="d-flex justify-content-between gap-2 py-1">
                <span>{{ $line['item_name'] }} × {{ $line['quantity'] }}</span>
                <span>{{ desk_money((float) $line['unit_price'] * (int) $line['quantity'], $line['currency']) }}</span>
            </div>
        @empty
            <p class="desk-muted">Add at least one item to the cart.</p>
        @endforelse
        <div class="desk-cart__total">
            <span>Total</span>
            <span>{{ desk_money($total, $cart['currency']) }}</span>
        </div>
        @foreach($cart['lines'] as $index => $line)
            <button class="btn btn-link p-0" type="submit" form="drop-{{ $index }}">Remove {{ $line['item_name'] }}</button>
        @endforeach
        <div class="field mt-3">
            <label for="guest_name">Guest</label>
            <input id="guest_name" name="guest_name" value="{{ old('guest_name', $cart['guest_name']) }}" required>
        </div>
        @if($profile->requires_reservation)
            <div class="field">
                <label for="restaurant_reservation_id">Reservation</label>
                <select id="restaurant_reservation_id" name="restaurant_reservation_id">
                    <option value="">Choose a reservation</option>
                    @foreach($reservations as $reservation)
                        <option value="{{ $reservation->id }}">{{ $reservation->guest_name }} · {{ $reservation->reserved_for->format('d M H:i') }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(auth()->user()->allowsDeskOperation('payments'))
            <div class="desk-pair">
                <div class="field">
                    <label for="pay_amount">Amount received</label>
                    <input id="pay_amount" name="pay_amount" type="number" min="0" step="0.01" value="{{ old('pay_amount') }}">
                </div>
                <div class="field">
                    <label for="method">Method</label>
                    <select id="method" name="method">
                        <option value="cash">Cash</option>
                        <option value="momo">MoMo</option>
                        <option value="card">Card</option>
                    </select>
                </div>
            </div>
        @endif
        @if($canRoom)
            <div class="field">
                <label for="booking_id">Charge balance to room</label>
                <select id="booking_id" name="booking_id">
                    <option value="">Do not charge a room</option>
                    @foreach($inHouse as $stay)
                        <option value="{{ $stay->id }}">{{ $stay->names }} · {{ $stay->room->title ?? 'Room' }} · {{ strtoupper($stay->currency) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($profile->momo_code)
            <p class="desk-meta">MoMo {{ $profile->momo_code }} · {{ $profile->momo_account_name }}</p>
        @endif
    </form>
    @foreach($cart['lines'] as $index => $line)
        <form id="drop-{{ $index }}" method="post" action="{{ route('desk.pos.sale.items.remove') }}">
            @csrf
            <input type="hidden" name="index" value="{{ $index }}">
        </form>
    @endforeach
</div>
@endsection
