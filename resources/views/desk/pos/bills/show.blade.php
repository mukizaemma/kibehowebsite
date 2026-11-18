@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', $bill->reference)
@section('content')
@php
    $canRoom = auth()->user()->hasDeskModule('front_office') && auth()->user()->hasDeskModule('pos');
    $settled = $bill->status !== 'cancelled' && $bill->balance() <= 0 && $bill->paid() > 0;
@endphp
<div class="desk-pagehead">
    <div>
        <h1>{{ $bill->reference }}</h1>
        <p>{{ $bill->guest_name }} · {{ $bill->status === 'cancelled' ? 'Cancelled' : 'Open' }}</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.pos.bills.pdf', $bill) }}">{{ $bill->reference }}.pdf</a>
</div>
<p>
    @if($bill->status === 'cancelled')
        <span class="desk-pass desk-pass--unpaid">Cancelled</span>
    @elseif($settled)
        <span class="desk-pass desk-pass--paid">Paid</span>
    @else
        <span class="desk-pass desk-pass--unpaid">Unpaid</span>
    @endif
</p>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="desk-card">
            <div class="table-responsive">
                <table class="desk-table">
                    <tbody>
                        @foreach($bill->lines as $line)
                            <tr>
                                <td class="stick">{{ $line->item_name }} × {{ $line->quantity }}</td>
                                <td class="num">{{ desk_money((float) $line->unit_price * (int) $line->quantity, $line->currency) }}</td>
                                <td class="actions">
                                    @if(auth()->user()->allowsDeskOperation('remove_line') && $bill->status !== 'cancelled')
                                        <a href="{{ route('desk.pos.bills.lines.remove', [$bill, $line]) }}">Remove</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>Balance</td>
                            <td class="num">{{ desk_money($bill->balance(), $bill->currency) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        @if(auth()->user()->allowsDeskOperation('payments') && $bill->status !== 'cancelled' && $bill->balance() > 0)
            <form class="desk-card desk-form mb-3" method="post" action="{{ route('desk.pos.bills.payments.store', $bill) }}">
                @csrf
                <h2 class="h6">Take payment</h2>
                <div class="field">
                    <label for="amount">Amount ({{ strtoupper($bill->currency) }})</label>
                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" required>
                </div>
                <div class="field">
                    <label for="method">Method</label>
                    <select id="method" name="method"><option value="cash">Cash</option><option value="momo">MoMo</option><option value="card">Card</option></select>
                </div>
                <button class="btn btn-outline-dark" type="submit">Save payment</button>
            </form>
        @endif
        @if($bill->roomPaid() > 0)
            <p class="desk-meta">Charged to room {{ desk_money($bill->roomPaid(), $bill->currency) }}. This is not counted as cash.</p>
        @endif
        @if($canRoom && $bill->status !== 'cancelled' && $bill->balance() > 0 && $inHouse->isNotEmpty())
            <form class="desk-card desk-form mb-3" method="post" action="{{ route('desk.pos.bills.charge-room', $bill) }}">
                @csrf
                <h2 class="h6">Charge to room</h2>
                <div class="field">
                    <label for="booking_id">In-house stay</label>
                    <select id="booking_id" name="booking_id" required>
                        @foreach($inHouse as $stay)
                            <option value="{{ $stay->id }}">{{ $stay->names }} · {{ strtoupper($stay->currency) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-dark" type="submit">Charge to room</button>
            </form>
        @endif
        @if(auth()->user()->allowsDeskOperation('cancel_bill') && $bill->status !== 'cancelled')
            <a class="btn btn-outline-dark" href="{{ route('desk.pos.bills.cancel', $bill) }}">Cancel bill</a>
        @endif
    </div>
</div>
@endsection
