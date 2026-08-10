@extends('layouts.adminBase')

@section('content')
<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="bg-white rounded shadow-sm border p-4 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h4 class="mb-0">Recent Bookings</h4>
            </div>

            <form method="GET" action="{{ route('content-management.reservations') }}" class="row g-3 align-items-end mb-0">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1" for="start_date">Start Date:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1" for="end_date">End Date:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-sm-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-warning text-dark px-4">Search</button>
                    @if(filled($startDate) || filled($endDate))
                        <a href="{{ route('content-management.reservations') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border p-3 h-100">
                    <div class="text-warning text-uppercase small fw-semibold">Reservations</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border p-3 h-100">
                    <div class="text-warning text-uppercase small fw-semibold">WhatsApp</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($stats['whatsapp']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border p-3 h-100">
                    <div class="text-warning text-uppercase small fw-semibold">Email</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($stats['email']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border p-3 h-100">
                    <div class="text-warning text-uppercase small fw-semibold">Confirmed</div>
                    <div class="fs-3 fw-bold mt-1 text-success">{{ number_format($stats['confirmed']) }}</div>
                    <div class="small text-warning mt-1">{{ number_format($stats['pending']) }} pending</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded shadow-sm border p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer Names</th>
                            <th>Customer Phone</th>
                            <th>Channel</th>
                            <th>Room</th>
                            <th>CheckIn Date</th>
                            <th>CheckOut Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            @php
                                $channel = str_contains(strtolower((string) ($booking->message ?? '')), 'confirmation preference: whatsapp')
                                    ? 'WhatsApp'
                                    : 'Email';
                                $status = strtolower((string) ($booking->status ?? 'pending'));
                                $badgeClass = match ($status) {
                                    'confirmed' => 'bg-success',
                                    'cancelled', 'rejected' => 'bg-danger',
                                    'full-booked' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                                $roomLabel = $booking->room->title
                                    ?? $booking->facility->title
                                    ?? $booking->tourActivity->title
                                    ?? '—';
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ optional($booking->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div>{{ $booking->names }}</div>
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td class="text-nowrap">{{ $booking->phone ?: '—' }}</td>
                                <td>{{ $channel }}</td>
                                <td>{{ $roomLabel }}</td>
                                <td class="text-nowrap">{{ optional($booking->checkin_date)->format('Y-m-d') ?: '—' }}</td>
                                <td class="text-nowrap">{{ optional($booking->checkout_date)->format('Y-m-d') ?: '—' }}</td>
                                <td class="text-nowrap">
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('content-management.reservations.show', $booking->id) }}" class="btn btn-sm btn-warning" title="View / respond">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <form action="{{ route('content-management.reservations.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Delete this reservation?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger w-100" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No reservations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="mt-3">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
