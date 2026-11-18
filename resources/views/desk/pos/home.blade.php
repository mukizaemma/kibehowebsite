@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'Restaurant POS')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Restaurant POS</h1>
        <p>Open bills: {{ $open }}</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.pos.sale') }}">New POS sale</a>
</div>
@if($taken !== [])
    <section class="desk-stats mb-3">
        @foreach($taken as $currency => $amount)
            <article class="desk-card">
                <p class="desk-meta">Taken today {{ strtoupper($currency) }}</p>
                <p class="desk-stat__value">{{ desk_money($amount, $currency) }}</p>
            </article>
        @endforeach
    </section>
@endif
<nav class="desk-pos">
    <a class="desk-pos__card" href="{{ route('desk.pos.sale') }}"><span class="desk-pos__icon desk-pos__icon--sale"><i class="fas fa-cash-register"></i></span>New POS sale</a>
    <a class="desk-pos__card" href="{{ route('desk.pos.bills.index') }}"><span class="desk-pos__icon desk-pos__icon--invoice"><i class="fas fa-file-invoice"></i></span>Invoices</a>
    <a class="desk-pos__card" href="{{ route('desk.pos.reservations') }}"><span class="desk-pos__icon desk-pos__icon--dining"><i class="fas fa-utensils"></i></span>Reservations</a>
    <a class="desk-pos__card" href="{{ route('desk.pos.reports') }}"><span class="desk-pos__icon desk-pos__icon--report"><i class="fas fa-chart-bar"></i></span>Reports</a>
    <a class="desk-pos__card" href="{{ route('desk.pos.menu') }}"><span class="desk-pos__icon desk-pos__icon--menu"><i class="fas fa-book-open"></i></span>Menu</a>
    <a class="desk-pos__card" href="{{ route('desk.pos.settings') }}"><span class="desk-pos__icon desk-pos__icon--settings"><i class="fas fa-cog"></i></span>Settings</a>
</nav>
@endsection
