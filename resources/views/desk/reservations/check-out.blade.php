@extends('desk.layout')
@section('title', 'Check out')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Check out</h1>
        <p>{{ $booking->names }} leaves today, so tomorrow night is free.</p>
    </div>
</div>
<form class="desk-card desk-form" method="post" action="{{ route('desk.reservations.check-out.store', $booking) }}">
    @csrf
    <button class="btn btn-dark" type="submit">Check out</button>
</form>
@endsection
