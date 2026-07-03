<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Gallery</h4>
                    <p class="text-muted small mb-0">
                        All images are pulled automatically from <strong>Rooms</strong> and <strong>Services (Facilities)</strong>.
                        Select up to <strong>3</strong> to show on the home page above the footer.
                    </p>
                </div>
                <span class="badge bg-secondary align-self-center" id="homeGalleryCounter">
                    {{ count($homeFeaturedKeys) }} / 3 selected for home
                </span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            <form action="{{ route('content-management.gallery.home-featured') }}" method="POST" id="homeGalleryForm">
                @csrf

                @if($galleryImages->isEmpty())
                    <div class="alert alert-info mb-0">
                        No gallery images yet. Upload cover or gallery photos on the
                        <a href="{{ route('content-management.rooms') }}">Rooms</a> or
                        <a href="{{ route('content-management.facilities') }}">Services</a> pages.
                    </div>
                @else
                    <div class="row g-3 mb-4">
                        @foreach($galleryImages as $item)
                            @php $isFeatured = in_array($item['key'], $homeFeaturedKeys, true); @endphp
                            <div class="col-md-4 col-lg-3">
                                <label class="card h-100 gallery-admin-card {{ $isFeatured ? 'gallery-admin-card--selected' : '' }}">
                                    <div class="position-relative">
                                        <img src="{{ $item['url'] }}" class="card-img-top" alt="{{ $item['caption'] }}" style="height: 180px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <input
                                                type="checkbox"
                                                class="form-check-input gallery-home-checkbox"
                                                name="featured_keys[]"
                                                value="{{ $item['key'] }}"
                                                {{ $isFeatured ? 'checked' : '' }}
                                            >
                                        </div>
                                        @if($isFeatured)
                                            <span class="badge bg-primary position-absolute bottom-0 start-0 m-2 gallery-home-badge">Home</span>
                                        @endif
                                    </div>
                                    <div class="card-body py-2">
                                        <p class="card-text small mb-1 fw-semibold">{{ $item['caption'] ?: 'Untitled' }}</p>
                                        <p class="card-text small text-muted mb-0">{{ $item['source_label'] }}</p>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary" id="clearHomeGallery">Clear selection</button>
                        <button type="submit" class="btn btn-primary">Save home page gallery</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<style>
    .gallery-admin-card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .gallery-admin-card:hover {
        border-color: rgba(13, 110, 253, 0.35);
    }
    .gallery-admin-card--selected {
        border-color: #0d6efd;
        box-shadow: 0 0 0 1px #0d6efd;
    }
    .gallery-admin-card .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
</style>

<script>
(function () {
    var maxHome = 3;
    var form = document.getElementById('homeGalleryForm');
    if (!form) return;

    var counter = document.getElementById('homeGalleryCounter');
    var clearBtn = document.getElementById('clearHomeGallery');

    function selectedCount() {
        return form.querySelectorAll('.gallery-home-checkbox:checked').length;
    }

    function refreshUI() {
        var count = selectedCount();
        if (counter) {
            counter.textContent = count + ' / ' + maxHome + ' selected for home';
        }
        form.querySelectorAll('.gallery-admin-card').forEach(function (card) {
            var checkbox = card.querySelector('.gallery-home-checkbox');
            var badge = card.querySelector('.gallery-home-badge');
            var selected = checkbox && checkbox.checked;
            card.classList.toggle('gallery-admin-card--selected', !!selected);
            if (selected && !badge) {
                var span = document.createElement('span');
                span.className = 'badge bg-primary position-absolute bottom-0 start-0 m-2 gallery-home-badge';
                span.textContent = 'Home';
                card.querySelector('.position-relative').appendChild(span);
            } else if (!selected && badge) {
                badge.remove();
            }
        });
    }

    form.addEventListener('change', function (e) {
        if (!e.target.classList.contains('gallery-home-checkbox')) return;
        if (selectedCount() > maxHome) {
            e.target.checked = false;
            alert('You can select up to 3 images for the home page gallery.');
        }
        refreshUI();
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            form.querySelectorAll('.gallery-home-checkbox:checked').forEach(function (cb) {
                cb.checked = false;
            });
            refreshUI();
        });
    }

    form.addEventListener('submit', function (e) {
        if (selectedCount() > maxHome) {
            e.preventDefault();
            alert('You can select up to 3 images for the home page gallery.');
        }
    });

    refreshUI();
})();
</script>
</div>
