@extends('desk.layout')
@section('title', 'Desk access')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Desk access</h1>
        <p>Turn front office and POS on for staff. Guest accounts cannot be granted either module.</p>
    </div>
</div>
@foreach($users as $person)
    <form class="desk-card desk-form mb-3" method="post" action="{{ route('desk.access.update', $person) }}">
        @csrf
        <h2 class="h6 mb-1">{{ $person->name }}</h2>
        <p class="desk-meta">{{ $person->email }} · {{ $person->role->name ?? 'No role' }}</p>
        @if($person->isGuest())
            <p class="mb-0">A guest account cannot be granted hotel desk access.</p>
        @else
            <div class="d-flex flex-wrap gap-3 my-2">
                @foreach($modules as $key => $label)
                    <label class="desk-check">
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" @checked($person->moduleGrants->contains('module', $key))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="desk-meta">Hide these actions</p>
            <div class="d-flex flex-wrap gap-3">
                @foreach($operations as $key => $label)
                    <label class="desk-check">
                        <input type="checkbox" name="denials[]" value="{{ $key }}" @checked($person->operationDenials->contains('operation', $key))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @if($person->isSuperAdmin())
                <p class="desk-meta mt-2">Super admin can open every page even when these boxes are ticked for someone else.</p>
            @endif
            <button class="btn btn-dark mt-2" type="submit">Save access</button>
        @endif
    </form>
@endforeach
@endsection
