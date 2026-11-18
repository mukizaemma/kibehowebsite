@extends('desk.layout')
@section('title', 'Front office')
@section('content')
@php $withRestaurant = auth()->user()->hasDeskModule('pos'); @endphp
<div class="desk-pagehead">
    <div>
        <h1>Front office</h1>
        <p>Today’s stays, arrivals, and rooms still held.</p>
    </div>
    <a class="btn btn-dark" href="{{ route('desk.reservations.create') }}">New reservation</a>
</div>
<section class="desk-stats {{ $withRestaurant ? 'desk-stats--with-restaurant' : '' }}">
    <article class="desk-card"><p class="desk-meta">Arrivals</p><p class="desk-stat__value">{{ $stats['arrivals'] }}</p></article>
    <article class="desk-card"><p class="desk-meta">In house</p><p class="desk-stat__value">{{ $stats['in_house'] }}</p></article>
    <article class="desk-card"><p class="desk-meta">Departures</p><p class="desk-stat__value">{{ $stats['departures'] }}</p></article>
    <article class="desk-card"><p class="desk-meta">Held tonight</p><p class="desk-stat__value">{{ $stats['held_tonight'] }}</p></article>
    @if($withRestaurant)
        <a class="desk-card" href="{{ route('desk.pos.home') }}" style="text-decoration:none;color:inherit;">
            <p class="desk-meta">Restaurant</p>
            <p class="desk-stat__value">POS</p>
        </a>
    @endif
</section>
@endsection
