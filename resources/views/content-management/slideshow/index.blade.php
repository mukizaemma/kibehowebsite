<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Slideshow Management</h4>
                    <p class="text-muted small mb-0">Hero images cycle behind fixed homepage text. Edit the message below; images still come from slides.</p>
                    <p class="text-muted small mb-0"><i class="fa fa-arrows-alt me-1"></i>Drag a card by its handle to change slide order.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#slideModal" data-toggle="modal" data-target="#slideModal" onclick="resetForm()">
                    <i class="fa fa-plus me-2"></i>Add New Slide
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-1">Homepage hero text</h5>
                    <p class="text-muted small mb-3">Shown over every slide. Hotel name comes from Settings → Company name. Leave a field empty to use the default English/French translation.</p>
                    <form action="{{ route('content-management.slideshow.hero') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="home_hero_headline">Headline</label>
                            <input type="text"
                                   class="form-control"
                                   id="home_hero_headline"
                                   name="home_hero_headline"
                                   maxlength="255"
                                   value="{{ old('home_hero_headline', $setting?->home_hero_headline ?? '') }}"
                                   placeholder="{{ site_trans('home.hero_headline') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="home_hero_lead">Supporting text</label>
                            <textarea class="form-control"
                                      id="home_hero_lead"
                                      name="home_hero_lead"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="{{ site_trans('home.hero_lead') }}">{{ old('home_hero_lead', $setting?->home_hero_lead ?? '') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save hero text</button>
                    </form>
                </div>
            </div>

            <div id="slideReorderStatus" class="alert alert-info py-2 px-3 small mb-3" style="display:none;"></div>

            <div class="row" id="slidesGrid">
                @foreach($slides as $slide)
                @php
                    $caption = $slide->heading ?: $slide->subheading;
                @endphp
                <div class="col-md-4 mb-4 slide-card-col" data-slide-id="{{ $slide->id }}">
                    <div class="card h-100 position-relative">
                        <span class="slide-drag-handle badge bg-dark text-white" title="Drag to reorder" style="position:absolute;top:.5rem;left:.5rem;z-index:2;cursor:grab;padding:.4rem .55rem;">
                            <i class="fa fa-arrows-alt"></i>
                        </span>
                        @if($slide->media_type === 'video')
                            @if($slide->video_url)
                                <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fa fa-video text-white" style="font-size: 48px;"></i>
                                </div>
                            @elseif($slide->video_file)
                                <video class="card-img-top" style="height: 200px; object-fit: cover;" controls>
                                    <source src="{{ asset('storage/' . $slide->video_file) }}" type="video/mp4">
                                </video>
                            @endif
                        @else
                            <img src="{{ asset('storage/' . ($slide->image ?? 'slides/default.jpg')) }}" class="card-img-top" alt="Slide" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <p class="card-text flex-grow-1">{{ $caption ?: 'No caption' }}</p>
                            @if($slide->button || $slide->link)
                                <p class="small text-muted mb-2"><i class="fa fa-link me-1"></i>Custom button on this slide</p>
                            @endif
                            <div class="mt-auto">
                                <button type="button" class="btn btn-sm btn-warning" onclick="editSlide({{ $slide->id }})">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSlide({{ $slide->id }})">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="slideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            @php
                $storeAction = route('content-management.slideshow.store');
                $updateActionTemplate = route('content-management.slideshow.update', ['slide' => '__SLIDE_ID__']);
                $deleteActionTemplate = route('content-management.slideshow.destroy', ['slide' => '__SLIDE_ID__']);
                $slidesData = $slides->keyBy('id');
            @endphp
            <form id="slideForm" enctype="multipart/form-data" action="{{ $storeAction }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="slideFormMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="slideImageInput">Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="image" id="slideImageInput" accept="image/*" required>
                        @include('content-management.includes.image-upload-hint')
                        <div class="invalid-feedback">Please select an image.</div>
                        <p class="text-muted small mt-1 mb-0" id="slideImageCurrentHint" style="display:none;">Leave empty to keep the current image when editing.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="slideCaption">Caption</label>
                        <textarea class="form-control" name="caption" id="slideCaption" rows="3" maxlength="500" placeholder="Short text shown over the slide (optional)"></textarea>
                    </div>
                    <details class="mb-1">
                        <summary class="text-muted small" style="cursor:pointer;">Custom button for this slide only (optional)</summary>
                        <div class="pt-3">
                            <div class="mb-3">
                                <label class="form-label" for="slideButtonText">Button text</label>
                                <input type="text" class="form-control" name="button" id="slideButtonText" maxlength="255" placeholder="Uses default from Settings if empty">
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="slideButtonLink">Button URL</label>
                                <input type="url" class="form-control" name="link" id="slideButtonLink" placeholder="Uses reservation URL from Settings if empty">
                            </div>
                        </div>
                    </details>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteSlideForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
    const slidesData = @json($slidesData);
    const storeAction = @json($storeAction);
    const updateActionTemplate = @json($updateActionTemplate);
    const deleteActionTemplate = @json($deleteActionTemplate);

    function resetForm() {
        const form = document.getElementById('slideForm');
        form.reset();
        form.action = storeAction;
        document.getElementById('slideFormMethod').value = 'POST';
        document.querySelector('#slideModal .modal-title').textContent = 'Add New Slide';
        document.getElementById('slideImageInput').setAttribute('required', 'required');
        document.getElementById('slideImageCurrentHint').style.display = 'none';
    }

    function editSlide(id) {
        const slide = slidesData[id];
        if (!slide) return;

        const form = document.getElementById('slideForm');
        document.getElementById('slideFormMethod').value = 'POST';
        form.action = updateActionTemplate.replace('__SLIDE_ID__', id);
        document.querySelector('#slideModal .modal-title').textContent = 'Edit Slide';

        document.getElementById('slideCaption').value = slide.heading || slide.subheading || '';
        document.getElementById('slideButtonText').value = slide.button || '';
        document.getElementById('slideButtonLink').value = slide.link || '';
        document.getElementById('slideImageInput').value = '';
        document.getElementById('slideImageInput').removeAttribute('required');
        document.getElementById('slideImageCurrentHint').style.display = 'block';

        CmsAdmin.showModal('slideModal');
    }

    function deleteSlide(id) {
        if (!confirm('Are you sure you want to delete this slide?')) {
            return;
        }
        const form = document.getElementById('deleteSlideForm');
        form.action = deleteActionTemplate.replace('__SLIDE_ID__', id);
        form.submit();
    }

    const reorderAction = @json(route('content-management.slideshow.reorder'));

    (function initSlideReorder() {
        const grid = document.getElementById('slidesGrid');
        if (!grid || grid.dataset.reorderReady === '1') {
            return;
        }

        function start() {
            if (typeof Sortable === 'undefined') {
                setTimeout(start, 200);
                return;
            }
            grid.dataset.reorderReady = '1';

            Sortable.create(grid, {
                handle: '.slide-drag-handle',
                animation: 150,
                ghostClass: 'slide-card-ghost',
                onEnd: persistOrder,
            });
        }

        function persistOrder() {
            const order = Array.from(grid.querySelectorAll('.slide-card-col'))
                .map((el) => parseInt(el.dataset.slideId, 10))
                .filter((id) => !Number.isNaN(id));

            const status = document.getElementById('slideReorderStatus');
            if (status) {
                status.style.display = 'block';
                status.className = 'alert alert-info py-2 px-3 small mb-3';
                status.textContent = 'Saving new order…';
            }

            CmsAdmin.fetchJson(reorderAction, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order: order }),
            }).then(function () {
                if (status) {
                    status.className = 'alert alert-success py-2 px-3 small mb-3';
                    status.textContent = 'Slide order saved.';
                    setTimeout(function () { status.style.display = 'none'; }, 2000);
                }
            }).catch(function () {
                if (status) {
                    status.className = 'alert alert-danger py-2 px-3 small mb-3';
                    status.textContent = 'Could not save the new order. Please try again.';
                }
            });
        }

        start();
    })();
</script>

@once
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endonce
</div>
