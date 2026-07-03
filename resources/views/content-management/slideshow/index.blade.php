<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Slideshow Management</h4>
                    <p class="text-muted small mb-0">Each slide needs an image and optional caption. Button text and URL use <a href="{{ route('setting') }}">Settings → Booking &amp; review links</a> unless you override a single slide.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#slideModal" data-toggle="modal" data-target="#slideModal" onclick="resetForm()">
                    <i class="fa fa-plus me-2"></i>Add New Slide
                </button>
            </div>

            <div class="row">
                @foreach($slides as $slide)
                @php
                    $caption = $slide->heading ?: $slide->subheading;
                @endphp
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
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
</script>
</div>
