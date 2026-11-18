<table>
    <thead>
        <tr><th>Guest</th><th>Status</th><th>Arrive</th><th class="num">Balance</th></tr>
    </thead>
    <tbody>
        @foreach($reservations as $reservation)
            <tr>
                <td>{{ $reservation->names }}</td>
                <td>{{ $reservation->deskStateLabel() }}</td>
                <td>{{ optional($reservation->checkin_date)->toDateString() }}</td>
                <td class="num">{{ desk_money((float) $reservation->balance_amount, $reservation->currency) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
