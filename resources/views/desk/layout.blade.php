@php
    $deskNav = $deskNav ?? 'front';
    $user = auth()->user();
    $company = optional(\App\Models\Setting::query()->first())->company ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Desk') — {{ $company }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('admin/css/desk.css') }}" rel="stylesheet">
</head>
<body>
<div class="desk-app">
    <header class="desk-top" data-desk-top>
        <a class="desk-brand" href="{{ $deskNav === 'pos' ? route('desk.pos.home') : route('desk.home') }}">{{ $company }}</a>
        <nav class="desk-nav" id="desk-nav">
            @if($deskNav === 'pos')
                <a href="{{ route('desk.pos.sale') }}" @if(request()->routeIs('desk.pos.sale')) aria-current="page" @endif>Sale</a>
                <a href="{{ route('desk.pos.bills.index') }}" @if(request()->routeIs('desk.pos.bills.*')) aria-current="page" @endif>Invoices</a>
                <a href="{{ route('desk.pos.reservations') }}" @if(request()->routeIs('desk.pos.reservations')) aria-current="page" @endif>Reservations</a>
                <a href="{{ route('desk.pos.reports') }}" @if(request()->routeIs('desk.pos.reports')) aria-current="page" @endif>Reports</a>
                <a href="{{ route('desk.pos.menu') }}" @if(request()->routeIs('desk.pos.menu')) aria-current="page" @endif>Menu</a>
                <a href="{{ route('desk.pos.settings') }}" @if(request()->routeIs('desk.pos.settings')) aria-current="page" @endif>Settings</a>
            @else
                <a href="{{ route('desk.home') }}" @if(request()->routeIs('desk.home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('desk.reservations.index') }}" @if(request()->routeIs('desk.reservations.*')) aria-current="page" @endif>Reservations</a>
                <a href="{{ route('desk.calendar') }}" @if(request()->routeIs('desk.calendar')) aria-current="page" @endif>Calendar</a>
                <a href="{{ route('desk.inventory') }}" @if(request()->routeIs('desk.inventory')) aria-current="page" @endif>Inventory</a>
                <a href="{{ route('desk.reports') }}" @if(request()->routeIs('desk.reports')) aria-current="page" @endif>Reports</a>
                <a href="{{ route('desk.rates') }}" @if(request()->routeIs('desk.rates')) aria-current="page" @endif>Rates</a>
                <a href="{{ route('desk.invoice-settings') }}" @if(request()->routeIs('desk.invoice-settings')) aria-current="page" @endif>Invoices</a>
                @if($user->hasDeskModule('pos'))
                    <a href="{{ route('desk.pos.home') }}">POS</a>
                @endif
            @endif
            @if($user->isSuperAdmin())
                <a href="{{ route('desk.access') }}">Access</a>
            @endif
            @if($user->isAdmin())
                <a href="{{ route('content-management.dashboard') }}">Website</a>
            @endif
        </nav>
        <button type="button" class="desk-nav-toggle" aria-label="Open menu" aria-controls="desk-nav" data-desk-toggle>Menu</button>
    </header>
    <main class="desk-app__body">
        @include('desk.partials.alerts')
        @yield('content')
    </main>
</div>
<script>
    document.querySelector('[data-desk-toggle]')?.addEventListener('click', function () {
        document.querySelector('[data-desk-top]')?.classList.toggle('is-open');
    });
</script>
@stack('scripts')
</body>
</html>
