@extends('desk.layout')
@section('title', 'Invoice settings')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Invoice settings</h1>
        <p>Used on stay receipts and restaurant bills.</p>
    </div>
    <button class="btn btn-dark" type="submit" form="invoice-form">Save</button>
</div>
<form id="invoice-form" class="desk-card desk-form" method="post" action="{{ route('desk.invoice-settings.update') }}">
    @csrf
    <div class="field">
        <label for="heading">Heading</label>
        <input id="heading" name="heading" value="{{ old('heading', $invoice->heading) }}">
    </div>
    <div class="field">
        <label for="footer">Footer</label>
        <textarea id="footer" name="footer">{{ old('footer', $invoice->footer) }}</textarea>
    </div>
</form>
@endsection
