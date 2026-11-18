<p>{{ $booking->names }} · {{ $booking->reference }}</p>
<p>{{ optional($booking->checkin_date)->toDateString() }} to {{ optional($booking->checkout_date)->toDateString() }} · {{ $booking->room->title ?? '' }}</p>
<table>
    <tr><td>Room total</td><td class="num">{{ desk_money((float) $booking->total_amount, $booking->currency) }}</td></tr>
    <tr><td>Paid</td><td class="num">{{ desk_money((float) $booking->paid_amount, $booking->currency) }}</td></tr>
    <tr class="total"><td>Balance</td><td class="num">{{ desk_money((float) $booking->balance_amount, $booking->currency) }}</td></tr>
</table>
@if($booking->relationLoaded('charges') && $booking->charges->isNotEmpty())
    <h2>Charges</h2>
    <table>
        @foreach($booking->charges as $charge)
            <tr>
                <td>{{ $charge->description }}</td>
                <td class="num">{{ desk_money((float) $charge->amount, $charge->currency) }}</td>
            </tr>
        @endforeach
    </table>
@endif
