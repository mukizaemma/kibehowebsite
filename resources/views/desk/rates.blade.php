@extends('desk.layout')
@section('title', 'Rates')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Rates</h1>
        <p>Nightly room price in the website currency.</p>
    </div>
    @if(auth()->user()->allowsDeskOperation('rates'))
        <button class="btn btn-dark" type="submit" form="rates-form">Save rates</button>
    @endif
</div>
<form id="rates-form" method="post" action="{{ route('desk.rates.update') }}">
    @csrf
    <div class="desk-card">
        <div class="table-responsive">
            <table class="desk-table">
                <thead><tr><th>Room</th><th class="num">Price / night</th></tr></thead>
                <tbody>
                    @foreach($rooms as $room)
                        <tr>
                            <td>{{ $room->title }}</td>
                            <td class="num"><input name="prices[{{ $room->id }}]" type="number" min="0" step="0.01" value="{{ $room->price }}" style="width:140px;min-height:44px;text-align:right;"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection
