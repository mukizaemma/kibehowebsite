@extends('desk.layout')
@section('title', 'Check in')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Check in</h1>
        <p>{{ $booking->names }} · {{ $booking->room->title ?? 'Room' }}</p>
    </div>
</div>
<form class="desk-card desk-form" method="post" action="{{ route('desk.reservations.check-in.store', $booking) }}">
    @csrf
    <p>The room stays held through the night before check-out.</p>
    <button class="btn btn-dark" type="submit">Check in</button>
</form>
@endsection
