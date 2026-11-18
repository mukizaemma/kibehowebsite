@extends('desk.layout')
@section('title', 'Add charge')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Charge to room</h1>
        <p>Added in {{ strtoupper($booking->currency) }} for {{ $booking->names }}.</p>
    </div>
</div>
<form class="desk-card desk-form" method="post" action="{{ route('desk.reservations.charges.store', $booking) }}">
    @csrf
    <div class="field">
        <label for="description">Description</label>
        <input id="description" name="description" required>
    </div>
    <div class="field">
        <label for="amount">Amount</label>
        <input id="amount" name="amount" type="number" min="0.01" step="0.01" required>
    </div>
    <button class="btn btn-dark" type="submit">Add charge</button>
</form>
@endsection
