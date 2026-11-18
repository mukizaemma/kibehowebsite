@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'POS settings')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Settings</h1>
        <p>Minimums are checked in the currency of the sale.</p>
    </div>
    @if(auth()->user()->allowsDeskOperation('settings'))
        <button class="btn btn-dark" type="submit" form="pos-settings">Save</button>
    @endif
</div>
<form id="pos-settings" class="desk-card desk-form" method="post" action="{{ route('desk.pos.settings.update') }}">
    @csrf
    <label class="desk-check">
        <input type="checkbox" name="requires_reservation" value="1" @checked(old('requires_reservation', $profile->requires_reservation))>
        Requires a reservation
    </label>
    <div class="desk-pair">
        <div class="field">
            <label for="minimum_amount">Minimum USD</label>
            <input id="minimum_amount" name="minimum_amount" type="number" min="0" step="0.01" value="{{ old('minimum_amount', $profile->minimum_amount) }}">
        </div>
        <div class="field">
            <label for="minimum_amount_local">Minimum RWF</label>
            <input id="minimum_amount_local" name="minimum_amount_local" type="number" min="0" step="1" value="{{ old('minimum_amount_local', $profile->minimum_amount_local) }}">
        </div>
    </div>
    <div class="desk-pair">
        <div class="field">
            <label for="momo_code">MoMo code</label>
            <input id="momo_code" name="momo_code" value="{{ old('momo_code', $profile->momo_code) }}">
        </div>
        <div class="field">
            <label for="momo_account_name">MoMo account name</label>
            <input id="momo_account_name" name="momo_account_name" value="{{ old('momo_account_name', $profile->momo_account_name) }}">
        </div>
    </div>
</form>
@endsection