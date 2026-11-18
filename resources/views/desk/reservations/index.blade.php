@extends('desk.layout')
@section('title', 'Reservations')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Reservations</h1>
        <p>Room stays held by the desk and the website.</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.reservations.create') }}">New reservation</a>
</div>
<div class="desk-card">
    <div class="table-responsive">
        <table class="desk-table">
            <thead>
                <tr>
                    <th class="stick">Guest</th>
                    <th>Room</th>
                    <th>Arrive</th>
                    <th>Leave</th>
                    <th>Status</th>
                    <th class="num">Balance</th>
                    <th class="actions">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td class="stick">{{ $reservation->names }}</td>
                        <td>{{ $reservation->room->title ?? '—' }}</td>
                        <td>{{ optional($reservation->checkin_date)->toDateString() }}</td>
                        <td>{{ optional($reservation->checkout_date)->toDateString() }}</td>
                        <td>{{ $reservation->deskStateLabel() }}</td>
                        <td class="num">{{ desk_money((float) $reservation->balance_amount, $reservation->currency) }}</td>
                        <td class="actions"><a class="btn btn-outline-dark btn-sm" href="{{ route('desk.reservations.show', $reservation) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td class="desk-empty" colspan="7">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $reservations->links() }}</div>
<p class="mt-3 no-print"><a href="{{ route('desk.reservations.pdf') }}">Download room-reservations.pdf</a></p>
@endsection
