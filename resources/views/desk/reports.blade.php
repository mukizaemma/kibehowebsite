@extends('desk.layout')
@section('title', 'Reports')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Reports</h1>
        <p>USD and RWF stay in their own totals.</p>
    </div>
</div>
<form class="desk-form desk-pair mb-3" method="get">
    <div class="field">
        <label for="start">From</label>
        <input id="start" type="date" name="start" value="{{ $start }}">
    </div>
    <div class="field">
        <label for="end">To</label>
        <input id="end" type="date" name="end" value="{{ $end }}">
    </div>
    <button class="btn btn-outline-dark" type="submit">Show</button>
</form>
<section class="desk-stats">
    @forelse($totals as $currency => $amount)
        <article class="desk-card">
            <p class="desk-meta">Room sales {{ strtoupper($currency) }}</p>
            <p class="desk-stat__value">{{ desk_money($amount, $currency) }}</p>
        </article>
    @empty
        <article class="desk-card"><p class="desk-muted">No stays in this range.</p></article>
    @endforelse
</section>
<div class="desk-card">
    <div class="table-responsive">
        <table class="desk-table">
            <thead>
                <tr><th class="stick">Guest</th><th>Status</th><th class="num">Total</th></tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td class="stick">{{ $booking->names }}</td>
                        <td>{{ $booking->deskStateLabel() }}</td>
                        <td class="num">{{ desk_money((float) $booking->total_amount, $booking->currency) }}</td>
                    </tr>
                @empty
                    <tr><td class="desk-empty" colspan="3">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
