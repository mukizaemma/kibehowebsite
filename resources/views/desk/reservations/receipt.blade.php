@extends('desk.layout')
@section('title', $booking->reference ?: 'Receipt')
@section('content')
<div class="desk-pagehead no-print">
    <div>
        <h1>Receipt</h1>
        <p>{{ $booking->reference }}</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.reservations.receipt.pdf', $booking) }}">{{ $booking->reference }}.pdf</a>
</div>
<div class="desk-card">
    <h2 class="h5">{{ $invoice->heading ?: 'Invoice' }}</h2>
    @include('desk.print.stay', ['booking' => $booking])
    @if($invoice->footer)
        <p class="desk-muted mt-3">{{ $invoice->footer }}</p>
    @endif
</div>
@endsection
