@extends('desk.layout')
@section('title', $booking ? 'Edit reservation' : 'New reservation')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>{{ $booking ? 'Edit reservation' : 'New reservation' }}</h1>
        <p>{{ $booking ? 'Change the room or the nights.' : 'Hold a room for these nights.' }}</p>
    </div>
    <button class="btn btn-dark" type="submit" form="stay-form">{{ $booking ? 'Save changes' : 'Save reservation' }}</button>
</div>
<form id="stay-form" class="desk-form" method="post" action="{{ $booking ? route('desk.reservations.update', $booking) : route('desk.reservations.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="desk-card">
                <div class="desk-pair">
                    <div class="field">
                        <label for="names">Guest name</label>
                        <input id="names" name="names" value="{{ old('names', $booking->names ?? '') }}" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $booking->phone ?? '') }}">
                    </div>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $booking->email ?? '') }}">
                </div>
                <div class="field">
                    <label for="room_id">Suite</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Choose a suite</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" data-price="{{ $room->price }}" @selected((string) old('room_id', $booking->room_id ?? '') === (string) $room->id)>{{ $room->title }}</option>
                        @endforeach
                    </select>
                    @error('room_id')<p class="text-danger small mb-0">{{ $message }}</p>@enderror
                </div>
                <div class="desk-pair">
                    <div class="field desk-field">
                        <label for="checkin">Check-in</label>
                        <input id="checkin" name="checkin" type="date" value="{{ old('checkin', $booking?->checkin_date?->toDateString()) }}" required data-desk-date>
                        @error('checkin')<p class="text-danger small mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="field desk-field">
                        <label for="checkout">Check-out</label>
                        <input id="checkout" name="checkout" type="date" value="{{ old('checkout', $booking?->checkout_date?->toDateString()) }}" required>
                        @error('checkout')<p class="text-danger small mb-0">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="desk-pair">
                    <div class="field">
                        <label for="adults">Adults</label>
                        <input id="adults" name="adults" type="number" min="1" value="{{ old('adults', $booking->adults ?? 1) }}">
                    </div>
                    <div class="field">
                        <label for="children">Children</label>
                        <input id="children" name="children" type="number" min="0" value="{{ old('children', $booking->children ?? 0) }}">
                    </div>
                </div>
                <div class="field">
                    <label for="rooms">Rooms</label>
                    <input id="rooms" name="rooms" type="number" min="1" value="{{ old('rooms', $booking->rooms ?? 1) }}">
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="desk-card">
                <h2 class="h6">Quote</h2>
                <ul class="desk-quote" id="desk-quote">
                    <li><span>Nights</span><span data-quote="nights">{{ $quote['nights'] ?? '—' }}</span></li>
                    <li><span>Nightly</span><span data-quote="nightly">{{ isset($quote) ? desk_money($quote['nightly'], $quote['currency']) : '—' }}</span></li>
                    <li><span>Total</span><span data-quote="total">{{ isset($quote) ? desk_money($quote['total'], $quote['currency']) : '—' }}</span></li>
                </ul>
                <p class="desk-meta mt-2">The saved total uses the room rate for this stay.</p>
            </div>
        </div>
    </div>
</form>
@endsection
@push('scripts')
<script>
    (function () {
        var checkin = document.getElementById('checkin');
        if (!checkin) return;
        var pop = document.createElement('div');
        pop.className = 'desk-popover';
        pop.hidden = true;
        pop.innerHTML = '<p class="desk-meta">Pick check-in, then check-out.</p><div class="desk-popover__days"></div>';
        checkin.parentElement.appendChild(pop);
        var grid = pop.querySelector('.desk-popover__days');
        var start = new Date();
        start.setHours(0, 0, 0, 0);
        function iso(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        for (var i = 0; i < 28; i++) {
            var day = new Date(start.getTime());
            day.setDate(start.getDate() + i);
            var button = document.createElement('button');
            button.type = 'button';
            button.textContent = String(day.getDate());
            button.dataset.iso = iso(day);
            button.addEventListener('click', function () {
                if (!checkin.value || document.getElementById('checkout').value) {
                    checkin.value = this.dataset.iso;
                    document.getElementById('checkout').value = '';
                } else if (this.dataset.iso > checkin.value) {
                    document.getElementById('checkout').value = this.dataset.iso;
                    pop.hidden = true;
                } else {
                    checkin.value = this.dataset.iso;
                }
                paint();
            });
            grid.appendChild(button);
        }
        function paint() {
            var a = checkin.value;
            var b = document.getElementById('checkout').value;
            grid.querySelectorAll('button').forEach(function (button) {
                button.classList.toggle('is-selected', button.dataset.iso === a || button.dataset.iso === b);
                button.classList.toggle('is-range', a && b && button.dataset.iso > a && button.dataset.iso < b);
            });
        }
        checkin.addEventListener('focus', function () { pop.hidden = false; paint(); });
        paint();
    })();
</script>
@endpush
