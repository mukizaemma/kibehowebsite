@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'Restaurant reservations')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Reservations</h1>
        <p>Table bookings. These do not hold a bedroom.</p>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="desk-card">
            <div class="table-responsive">
                <table class="desk-table">
                    <thead><tr><th class="stick">Guest</th><th>When</th><th>Party</th><th>Pass</th></tr></thead>
                    <tbody>
                        @forelse($reservations as $reservation)
                            @php
                                $openBills = $reservation->bills->where('status', '!=', 'cancelled');
                                $paid = $openBills->isNotEmpty() && $openBills->every(fn ($bill) => $bill->balance() <= 0);
                            @endphp
                            <tr>
                                <td class="stick">{{ $reservation->guest_name }}</td>
                                <td>{{ $reservation->reserved_for->format('d M H:i') }}</td>
                                <td>{{ $reservation->party_size }}</td>
                                <td>
                                    @if($openBills->isNotEmpty())
                                        <span class="desk-pass {{ $paid ? 'desk-pass--paid' : 'desk-pass--unpaid' }}">{{ $paid ? 'Paid' : 'Unpaid' }}</span>
                                    @else
                                        <span class="desk-muted">No bill</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="desk-empty" colspan="4">No reservations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <form class="desk-card desk-form" method="post" action="{{ route('desk.pos.reservations.store') }}">
            @csrf
            <h2 class="h6">New reservation</h2>
            <div class="field"><label for="guest_name">Guest</label><input id="guest_name" name="guest_name" required></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone"></div>
            <div class="desk-pair">
                <div class="field"><label for="party_size">Party</label><input id="party_size" name="party_size" type="number" min="1" value="2"></div>
                <div class="field"><label for="reserved_for">When</label><input id="reserved_for" name="reserved_for" type="datetime-local" required></div>
            </div>
            <button class="btn btn-dark" type="submit">Save reservation</button>
        </form>
    </div>
</div>
@endsection
