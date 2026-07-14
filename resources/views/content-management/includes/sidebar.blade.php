@php
$setting = App\Models\Setting::first();
$currentRoute = request()->route()->getName();

$isCmsOrLegacyDashboard = in_array($currentRoute, ['dashboard', 'content-management.dashboard'], true);
$isUpdatesAdmin = in_array($currentRoute, ['getBlogs', 'saveBlog', 'editBlog', 'viewBlog', 'updateBlog', 'deleteBlog', 'publishBlog'], true);
$isTeamAdmin = in_array($currentRoute, ['staff', 'saveStaff', 'editStaff', 'updateStaff', 'deleteStaff'], true);
$isSlidesAdmin = str_contains($currentRoute, 'content-management.slideshow');
$isPartnersAdmin = in_array($currentRoute, ['getPartners', 'savePartner', 'editPartner', 'updatePartner', 'destroyPartner'], true);
$isSettingsAdmin = in_array($currentRoute, ['setting', 'saveSetting', 'homePage', 'saveHome', 'aboutPage', 'saveAbout'], true) || str_starts_with((string) $currentRoute, 'setting.') || str_contains($currentRoute, 'content-management.site-translations');
$isDiningAdmin = $currentRoute === 'resto' || str_contains(strtolower((string) $currentRoute), 'resto');
$isEventsPageAdmin = $currentRoute === 'eventsPage' || str_contains(strtolower((string) $currentRoute), 'event') || str_contains(strtolower((string) $currentRoute), 'meeting');
@endphp

<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="{{ asset('storage/images') . ($setting->logo ?? '') }}" alt="" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <span>
                    @if(auth()->user()->isSuperAdmin())
                        <span class="badge bg-danger me-1">Super Admin</span>
                    @elseif(auth()->user()->isContentManager())
                        <span class="badge bg-primary me-1">Content Manager</span>
                    @endif
                    CMS
                </span>
            </div>
        </div>
        <div class="navbar-nav w-100" data-no-spa-navigate>
            <a href="{{ route('content-management.dashboard') }}" class="nav-item nav-link {{ $isCmsOrLegacyDashboard ? 'active' : '' }}">
                <i class="fas fa-grip-horizontal me-2"></i>Dashboard
            </a>

            <a href="{{ route('content-management.rooms') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.rooms') ? 'active' : '' }}">
                <i class="fas fa-bed me-2"></i>Rooms
            </a>

            <a href="{{ route('content-management.facilities') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.facilities') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell me-2"></i>Our Services
            </a>
            <a href="{{ route('content-management.kibeho-page.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.kibeho-page') ? 'active' : '' }}">
                <i class="fas fa-church me-2"></i>Kibeho page
            </a>
            <a href="{{ route('content-management.nyaruguru-page.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.nyaruguru-page') ? 'active' : '' }}">
                <i class="fas fa-mountain me-2"></i>Nyaruguru
            </a>
            <a href="{{ route('content-management.gikongoro-diocese-page.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.gikongoro-diocese-page') ? 'active' : '' }}">
                <i class="fas fa-cross me-2"></i>Diocese
            </a>
            <a href="{{ route('resto') }}" class="nav-item nav-link {{ $isDiningAdmin ? 'active' : '' }}">
                <i class="fas fa-utensils me-2"></i>Dining page
            </a>
            <a href="{{ route('eventsPage') }}" class="nav-item nav-link {{ $isEventsPageAdmin ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt me-2"></i>Meetings
            </a>
            <a href="{{ route('content-management.amenities') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.amenities') ? 'active' : '' }}">
                <i class="fas fa-list me-2"></i>Amenities
            </a>
            <a href="{{ route('content-management.tour-activities') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.tour-activities') ? 'active' : '' }}">
                <i class="fas fa-hiking me-2"></i>Activities
            </a>
            <a href="{{ route('content-management.why-choose-us.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.why-choose-us') ? 'active' : '' }}">
                <i class="fas fa-thumbs-up me-2"></i>Why Choose Us
            </a>
            <a href="{{ route('content-management.attractions.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.attractions') ? 'active' : '' }}">
                <i class="fas fa-map-pin me-2"></i>Attractions
            </a>

            <a href="{{ route('getBlogs') }}" class="nav-item nav-link {{ $isUpdatesAdmin ? 'active' : '' }}">
                <i class="fas fa-pen-nib me-2"></i>Updates
            </a>
            <a href="{{ route('staff') }}" class="nav-item nav-link {{ $isTeamAdmin ? 'active' : '' }}">
                <i class="fas fa-user-friends me-2"></i>Team
            </a>

            <a href="{{ route('content-management.gallery') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.gallery') ? 'active' : '' }}">
                <i class="fas fa-images me-2"></i>Gallery
            </a>
            <a href="{{ route('content-management.slideshow') }}" class="nav-item nav-link {{ $isSlidesAdmin ? 'active' : '' }}">
                <i class="fas fa-images me-2"></i>Slideshow
            </a>
            <a href="{{ route('content-management.home-journey.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.home-journey') ? 'active' : '' }}">
                <i class="fas fa-route me-2"></i>Pilgrimage journey
            </a>
            <a href="{{ route('content-management.page-heroes') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.page-heroes') ? 'active' : '' }}">
                <i class="fas fa-image me-2"></i>Page backgrounds
            </a>
            <a href="{{ route('getPartners') }}" class="nav-item nav-link {{ $isPartnersAdmin ? 'active' : '' }}">
                <i class="fas fa-handshake me-2"></i>Partners
            </a>

            <hr>

            <a href="{{ route('setting') }}" class="nav-item nav-link {{ $isSettingsAdmin ? 'active' : '' }}">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
            <a href="{{ route('content-management.site-translations.index') }}" class="nav-item nav-link {{ str_contains($currentRoute, 'content-management.site-translations') ? 'active' : '' }}">
                <i class="fas fa-language me-2"></i>Translations
            </a>
            <a href="{{ route('logouts') }}" class="nav-item nav-link">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </nav>
</div>
