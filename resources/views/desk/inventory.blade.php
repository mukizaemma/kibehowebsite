@extends('desk.layout')
@section('title', 'Inventory')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Inventory</h1>
        <p>Closures block a night. Overbook allows extra holds.</p>
    </div>
</div>
<div class="desk-card mb-3">
    <div class="table-responsive">
        <table class="desk-table">
            <thead>
                <tr><th>Room</th><th class="num">Rooms</th><th class="num">Overbook</th><th>Closures</th></tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->title }}</td>
                        <td class="num">{{ $room->number_of_rooms }}</td>
                        <td class="num">
                            <form method="post" action="{{ route('desk.inventory.overbook') }}" class="d-flex gap-2 justify-content-end">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <input name="overbook" type="number" min="0" max="20" value="{{ $room->overbook }}" style="width:80px;min-height:44px;">
                                <button class="btn btn-outline-dark btn-sm" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $room->closures->sortBy('night')->map(fn ($closure) => $closure->night->toDateString())->implode(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="desk-empty" colspan="4">No rooms yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<form class="desk-card desk-form" method="post" action="{{ route('desk.inventory.closures.store') }}">
    @csrf
    <h2 class="h6">Close a night</h2>
    <div class="desk-pair">
        <div class="field">
            <label for="room_id">Room</label>
            <select id="room_id" name="room_id" required>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="night">Night</label>
            <input id="night" type="date" name="night" required>
        </div>
    </div>
    <div class="field">
        <label for="note">Note</label>
        <input id="note" name="note">
    </div>
    <button class="btn btn-dark" type="submit">Save closure</button>
</form>
@endsection
