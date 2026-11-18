<p>{{ $bill->guest_name }} · {{ $bill->reference }}</p>
<table>
    @foreach($bill->lines as $line)
        <tr>
            <td>{{ $line->item_name }} × {{ $line->quantity }}</td>
            <td class="num">{{ desk_money((float) $line->unit_price * (int) $line->quantity, $line->currency) }}</td>
        </tr>
    @endforeach
    <tr><td>Paid</td><td class="num">{{ desk_money($bill->paid(), $bill->currency) }}</td></tr>
    <tr class="total"><td>Balance</td><td class="num">{{ desk_money($bill->balance(), $bill->currency) }}</td></tr>
</table>
