@extends('desk.layout')
@section('title', 'Calendar')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Calendar</h1>
        <p>Each bar is one night on that room.</p>
    </div>
</div>
<form class="desk-form d-flex gap-2 align-items-end mb-3" method="get">
    <div class="field mb-0">
        <label for="start">From</label>
        <input id="start" type="date" name="start" value="{{ $start->toDateString() }}">
    </div>
    <button class="btn btn-outline-dark" type="submit">Show</button>
</form>
<div class="desk-legend">
    <span class="desk-bar desk-bar--reserved">Reserved</span>
    <span class="desk-bar desk-bar--checked_in">Checked in</span>
    <span class="desk-bar desk-bar--checked_out">Checked out</span>
    <span class="desk-bar desk-bar--closure">Closure</span>
</div>
<div class="desk-card desk-cal-wrap">
    <div class="desk-cal" style="--days: {{ $days->count() }}">
        <div class="desk-cal__cell desk-cal__room">Room</div>
        @foreach($days as $day)
            <div class="desk-cal__cell desk-meta">{{ $day->format('d M') }}</div>
        @endforeach
        @foreach($rooms as $room)
            <div class="desk-cal__cell desk-cal__room">{{ $room->title }}</div>
            @foreach($days as $day)
                @php
                    $night = $day->toDateString();
                    $closure = $closures->get($room->id.'|'.$night);
                    $matches = ($bookings->get($room->id) ?? collect())->filter(function ($booking) use ($night) {
                        if (!$booking->checkin_date || !$booking->checkout_date) {
                            return false;
                        }
                        if (in_array($booking->deskState(), ['cancelled', 'no_show'], true)) {
                            return false;
                        }
                        return $night >= $booking->checkin_date->toDateString() && $night < $booking->checkout_date->toDateString();
                    });
                    $active = $matches->filter(fn ($booking) => in_array($booking->deskState(), ['reserved', 'checked_in'], true));
                    $units = (int) $active->sum(fn ($booking) => max(1, (int) $booking->rooms));
                    $stay = $active->first() ?: $matches->first();
                    $label = $stay ? ($active->count() > 1 ? $units.' held' : $stay->names) : '';
                @endphp
                <div class="desk-cal__cell">
                    @if($closure)
                        <span class="desk-bar desk-bar--closure">Closed</span>
                    @elseif($stay)
                        <span class="desk-bar desk-bar--{{ $active->isNotEmpty() ? $stay->deskState() : 'checked_out' }}" title="{{ $active->pluck('names')->implode(', ') }}">{{ $label }}</span>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@endsection
