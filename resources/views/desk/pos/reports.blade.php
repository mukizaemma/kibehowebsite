@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'Restaurant report')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Reports</h1>
        <p>Cash taken and room charges are kept apart, and USD is not added to RWF.</p>
    </div>
</div>
<section class="desk-stats">
    @foreach($cash as $currency => $amount)
        <article class="desk-card">
            <p class="desk-meta">Cash {{ strtoupper($currency) }}</p>
            <p class="desk-stat__value">{{ desk_money($amount, $currency) }}</p>
        </article>
    @endforeach
    @foreach($onRoom as $currency => $amount)
        <article class="desk-card">
            <p class="desk-meta">On room {{ strtoupper($currency) }}</p>
            <p class="desk-stat__value">{{ desk_money($amount, $currency) }}</p>
        </article>
    @endforeach
    @if($cash === [] && $onRoom === [])
        <article class="desk-card"><p class="desk-muted">No restaurant sales yet.</p></article>
    @endif
</section>
@endsection
