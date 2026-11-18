@extends('desk.layout')
@section('title', $booking->names)
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>{{ $booking->names }}</h1>
        <p>{{ $booking->deskStateLabel() }} · {{ $booking->reference }} · {{ optional($booking->checkin_date)->toDateString() }} to {{ optional($booking->checkout_date)->toDateString() }}</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.reservations.receipt', $booking) }}">Receipt</a>
</div>
<div class="desk-actions d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-dark" href="{{ route('desk.reservations.edit', $booking) }}">Edit</a>
    @if(in_array($booking->status, ['reserved', 'pending', 'confirmed'], true))
        <a class="btn btn-outline-dark" href="{{ route('desk.reservations.check-in', $booking) }}">Check in</a>
    @endif
    @if($booking->status === 'checked_in')
        <a class="btn btn-outline-dark" href="{{ route('desk.reservations.check-out', $booking) }}">Check out</a>
    @endif
    @if(auth()->user()->allowsDeskOperation('cancel_reservation') && !in_array($booking->deskState(), ['cancelled', 'checked_out'], true))
        <a class="btn btn-outline-dark" href="{{ route('desk.reservations.cancel', $booking) }}">Cancel</a>
    @endif
    @if(auth()->user()->allowsDeskOperation('no_show') && in_array($booking->status, ['reserved', 'pending', 'confirmed'], true))
        <a class="btn btn-outline-dark" href="{{ route('desk.reservations.no-show', $booking) }}">No-show</a>
    @endif
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="desk-card">
            <h2 class="h6">Stay</h2>
            <ul class="desk-quote">
                <li><span>Room</span><span>{{ $booking->room->title ?? '—' }}</span></li>
                <li><span>Guests</span><span>{{ $booking->adults }} adults, {{ $booking->children }} children</span></li>
                <li><span>Rooms</span><span>{{ $booking->rooms }}</span></li>
                <li><span>Total</span><span>{{ desk_money((float) $booking->total_amount, $booking->currency) }}</span></li>
                <li><span>Paid</span><span>{{ desk_money((float) $booking->paid_amount, $booking->currency) }}</span></li>
                <li><span>Balance</span><span>{{ desk_money((float) $booking->balance_amount, $booking->currency) }}</span></li>
            </ul>
        </div>
        <div class="desk-card mt-3">
            <h2 class="h6">Charges</h2>
            <div class="table-responsive">
                <table class="desk-table">
                    <tbody>
                        @forelse($booking->charges as $charge)
                            <tr>
                                <td class="stick">{{ $charge->description }}</td>
                                <td class="num">{{ desk_money((float) $charge->amount, $charge->currency) }}</td>
                                <td class="actions">
                                    @if(auth()->user()->allowsDeskOperation('remove_charge'))
                                        <a href="{{ route('desk.charges.remove', $charge) }}">Remove</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="desk-empty" colspan="3">No extra charges yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-2 mb-0"><a href="{{ route('desk.reservations.charges.create', $booking) }}">Add a charge</a></p>
        </div>
    </div>
    <div class="col-lg-5">
        @if(auth()->user()->allowsDeskOperation('payments'))
            <div class="desk-card">
                <h2 class="h6">Take payment</h2>
                <form class="desk-form" method="post" action="{{ route('desk.reservations.payments.store', $booking) }}">
                    @csrf
                    <div class="field">
                        <label for="amount">Amount ({{ strtoupper($booking->currency) }})</label>
                        <input id="amount" name="amount" type="number" min="0.01" step="0.01" required>
                    </div>
                    <div class="field">
                        <label for="method">Method</label>
                        <select id="method" name="method">
                            <option value="cash">Cash</option>
                            <option value="momo">MoMo</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-dark" type="submit">Save payment</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
