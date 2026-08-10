@extends('layouts.adminBase')

@section('content')
@php
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
    $requestNote = trim(preg_replace('/\s*Confirmation preference:\s*(email|whatsapp)\s*/i', '', (string) ($booking->message ?? '')));
@endphp
<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <nav class="small text-muted mb-3" aria-label="Breadcrumb">
            <a href="{{ route('content-management.reservations') }}">Reservations</a>
            <span class="mx-1">/</span>
            <span>#{{ $booking->id }}</span>
        </nav>

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

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="bg-white rounded shadow-sm border p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Reservation #{{ $booking->id }}</h4>
                        <a href="{{ route('content-management.reservations') }}" class="btn btn-outline-secondary btn-sm">Back to Bookings</a>
                    </div>

                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 38%;">Submitted</th>
                                <td>{{ optional($booking->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Submitted via</th>
                                <td>{{ $channel }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Guest name</th>
                                <td>{{ $booking->names }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phone</th>
                                <td>{{ $booking->phone ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email</th>
                                <td>{{ $booking->email ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Country</th>
                                <td>{{ $booking->country ?: '—' }}</td>
                            </tr>
                            @if(filled($booking->stay_reason))
                            <tr>
                                <th class="text-muted">Reason for stay</th>
                                <td>{{ \App\Support\BookingStayReasons::label($booking->stay_reason) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Party</th>
                                <td>{{ ($booking->party_type ?? '') === 'group' ? 'Group / team' : 'Individual / couple' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Room</th>
                                <td>{{ $roomLabel }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Rooms requested</th>
                                <td>{{ (int) ($booking->rooms ?? 1) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Check-in</th>
                                <td>{{ optional($booking->checkin_date)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Check-out</th>
                                <td>{{ optional($booking->checkout_date)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Adults / Children</th>
                                <td>{{ (int) ($booking->adults ?? 0) }} / {{ (int) ($booking->children ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Payment</th>
                                <td>{{ $booking->payment_status ?: 'pay_at_hotel' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Total</th>
                                <td>{{ number_format((float) ($booking->total_amount ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Additional request</th>
                                <td>{{ $requestNote !== '' ? $requestNote : '—' }}</td>
                            </tr>
                            @if(filled($booking->admin_reply))
                            <tr>
                                <th class="text-muted">Last reply</th>
                                <td>
                                    <div class="small text-muted mb-1">{{ optional($booking->admin_replied_at)->format('Y-m-d H:i') }}</div>
                                    <div style="white-space: pre-wrap;">{{ $booking->admin_reply }}</div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="bg-white rounded shadow-sm border p-4">
                    <h5 class="mb-3">Update status &amp; notify guest</h5>
                    <form method="POST" action="{{ route('content-management.reservations.reply', $booking->id) }}" id="reservationReplyForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="reply_status">New status</label>
                            <select name="status" id="reply_status" class="form-select" required>
                                <option value="">Choose outcome...</option>
                                <option value="confirmed">Confirm</option>
                                <option value="cancelled">Reject / cancel</option>
                                <option value="full-booked">Full-booked</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning" for="admin_reply">Message to guest (optional)</label>
                            <textarea
                                name="admin_reply"
                                id="admin_reply"
                                class="form-control"
                                rows="12"
                                placeholder="This note is included in the guest email with the status update."
                            ></textarea>
                            <small class="text-muted">Prefills when you choose a status. Edit before sending.</small>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email_guest" id="email_guest" value="1" checked @disabled(!filled($booking->email))>
                            <label class="form-check-label" for="email_guest">
                                Email guest{{ filled($booking->email) ? ' ('.$booking->email.')' : ' (no email on file)' }}
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning text-dark">Save &amp; notify</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
(function () {
    var drafts = @json($drafts);
    var statusEl = document.getElementById('reply_status');
    var messageEl = document.getElementById('admin_reply');
    if (!statusEl || !messageEl) return;

    statusEl.addEventListener('change', function () {
        var key = statusEl.value === 'confirmed' ? 'confirmed' : 'cancelled';
        if (drafts && drafts[key]) {
            messageEl.value = drafts[key];
        }
    });
})();
</script>
@endsection
