@extends('desk.layout')
@section('title', $title)
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </div>
</div>
<form class="desk-form desk-card" method="post" action="{{ $action }}">
    @csrf
    <label class="desk-check" for="confirm">
        <input id="confirm" type="checkbox" name="confirm" value="1">
        I confirm
    </label>
    @error('confirm')<p class="text-danger">{{ $message }}</p>@enderror
    <button class="btn btn-dark" type="submit">{{ $submit }}</button>
</form>
@endsection
