@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'Invoices')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Invoices</h1>
        <p>Balances stay in the currency of the bill.</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.pos.sale') }}">New POS sale</a>
</div>
<div class="desk-card">
    <div class="table-responsive">
        <table class="desk-table">
            <thead>
                <tr>
                    <th class="stick">Guest</th>
                    <th>Reference</th>
                    <th class="num">Total</th>
                    <th class="num">Paid</th>
                    <th class="num">Balance</th>
                    <th class="actions">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td class="stick">{{ $bill->guest_name }}</td>
                        <td>{{ $bill->reference }}</td>
                        <td class="num">{{ desk_money($bill->total(), $bill->currency) }}</td>
                        <td class="num">{{ desk_money($bill->cashPaid(), $bill->currency) }}</td>
                        <td class="num">{{ desk_money($bill->balance(), $bill->currency) }}</td>
                        <td class="actions"><a class="btn btn-outline-dark btn-sm" href="{{ route('desk.pos.bills.show', $bill) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td class="desk-empty" colspan="6">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $bills->links() }}</div>
@endsection
