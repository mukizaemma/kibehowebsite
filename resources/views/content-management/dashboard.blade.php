<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')

<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session()->get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(auth()->user()->isSuperAdmin())
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong><i class="fa fa-crown me-2"></i>Super Admin Mode:</strong> You are currently working in Website Content Management.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @elseif(auth()->user()->isContentManager())
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <strong><i class="fa fa-user-cog me-2"></i>Content Manager Dashboard:</strong> You have access to manage website content including services, rooms, facilities, gallery, and page settings.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="mb-1">Website overview</h4>
                    <p class="text-muted mb-0">Manage public content for the hotel website. Guest bookings are handled through your external reservation channel.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('setting') }}#channel-links" class="btn btn-outline-primary">
                        <i class="fa fa-link me-2"></i>Booking channel
                    </a>
                    <a href="{{ route('content-management.rooms') }}" class="btn btn-primary">
                        <i class="fa fa-bed me-2"></i>Manage rooms
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border-start border-4 border-primary d-flex align-items-center justify-content-between p-4 h-100">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fa fa-bed fa-2x text-primary"></i>
                    </div>
                    <div class="ms-3 text-end">
                        <p class="mb-1 text-muted small">Total Rooms</p>
                        <h4 class="mb-0">{{ $stats['rooms'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border-start border-4 border-success d-flex align-items-center justify-content-between p-4 h-100">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fa fa-concierge-bell fa-2x text-success"></i>
                    </div>
                    <div class="ms-3 text-end">
                        <p class="mb-1 text-muted small">Total Services</p>
                        <h4 class="mb-0">{{ $stats['services'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border-start border-4 border-info d-flex align-items-center justify-content-between p-4 h-100">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="fa fa-building fa-2x text-info"></i>
                    </div>
                    <div class="ms-3 text-end">
                        <p class="mb-1 text-muted small">Total Facilities</p>
                        <h4 class="mb-0">{{ $stats['facilities'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded shadow-sm border-start border-4 border-warning d-flex align-items-center justify-content-between p-4 h-100">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="fa fa-users fa-2x text-warning"></i>
                    </div>
                    <div class="ms-3 text-end">
                        <p class="mb-1 text-muted small">Total Users</p>
                        <h4 class="mb-0">{{ $stats['users'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="bg-white rounded shadow-sm h-100 p-4">
                    <h5 class="mb-3">Quick actions</h5>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('content-management.rooms') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-bed text-primary me-2"></i>Rooms
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('content-management.services') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-concierge-bell text-success me-2"></i>Services
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('content-management.facilities') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-building text-info me-2"></i>Facilities
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('content-management.amenities') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-list text-warning me-2"></i>Amenities
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('content-management.gallery') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-images text-secondary me-2"></i>Gallery
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a href="{{ route('setting') }}" class="btn btn-light border w-100 py-3 text-start">
                                <i class="fa fa-cog text-dark me-2"></i>Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @unless($contentReady)
                <div class="bg-light rounded border border-warning border-opacity-25 p-4 h-100">
                    <h6 class="mb-2"><i class="fa fa-rocket text-warning me-2"></i>Getting started</h6>
                    <p class="small text-muted mb-3">Complete these steps so your public website is ready for guests.</p>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            @if($stats['rooms'] > 0)
                                <i class="fa fa-check-circle text-success me-2"></i>
                            @else
                                <i class="fa fa-circle text-muted me-2"></i>
                            @endif
                            Add room types and photos
                        </li>
                        <li class="mb-2">
                            @if($stats['services'] > 0)
                                <i class="fa fa-check-circle text-success me-2"></i>
                            @else
                                <i class="fa fa-circle text-muted me-2"></i>
                            @endif
                            Publish hotel services
                        </li>
                        <li class="mb-2">
                            @if($stats['facilities'] > 0)
                                <i class="fa fa-check-circle text-success me-2"></i>
                            @else
                                <i class="fa fa-circle text-muted me-2"></i>
                            @endif
                            List facilities and amenities
                        </li>
                        <li>
                            <i class="fa fa-circle text-muted me-2"></i>
                            Set your external booking URL in Settings
                        </li>
                    </ul>
                </div>
                @else
                <div class="bg-light rounded border p-4 h-100">
                    <h6 class="mb-2"><i class="fa fa-external-link-alt text-primary me-2"></i>Reservations</h6>
                    <p class="small text-muted mb-3">This dashboard is for website content only. Room bookings are managed through your external reservation channel.</p>
                    <a href="{{ route('setting') }}#channel-links" class="btn btn-sm btn-outline-primary">Configure booking link</a>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>
</div>
