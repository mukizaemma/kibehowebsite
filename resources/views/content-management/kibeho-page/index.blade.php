@extends('layouts.adminBase')

@section('content')
<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Kibeho page</h4>
                    <p class="text-muted small mb-0">Manage the public Visit Kibeho Sanctuary page — content, activities, and gallery.</p>
                </div>
                <a href="{{ route('explore-kibeho') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-external-link-alt me-1"></i> View page
                </a>
            </div>

            <ul class="nav nav-tabs mb-4" id="kibehoPageTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="kibeho-tab-page" data-bs-toggle="tab" data-bs-target="#kibeho-panel-page" type="button" role="tab">Page content</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kibeho-tab-events" data-bs-toggle="tab" data-bs-target="#kibeho-panel-events" type="button" role="tab">Activities</button>
                </li>
            </ul>

            <div class="tab-content" id="kibehoPageTabContent">
                <div class="tab-pane fade show active" id="kibeho-panel-page" role="tabpanel">
                    <form id="kibehoPageForm" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label" for="kibeho_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kibeho_title" name="title" value="{{ $page->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="kibeho_description">Description</label>
                                    <textarea class="form-control" id="kibeho_description" name="description" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="kibeho_official_url">Official sanctuary website <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" class="form-control" id="kibeho_official_url" name="official_website_url" value="{{ $page->official_website_url }}" placeholder="https://www.kibeho.org/">
                                    <small class="text-muted">Leave blank if you do not want a link to an external site.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Cover image</label>
                                    @if($page->cover_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/'.$page->cover_image) }}" alt="" class="img-fluid rounded border" style="max-height:160px;object-fit:cover;width:100%;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" name="cover_image" accept="image/*">
                                    @include('content-management.includes.image-upload-hint')
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="Active" @selected($page->status === 'Active')>Active</option>
                                        <option value="Inactive" @selected($page->status === 'Inactive')>Inactive</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Save page</button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Gallery images</h5>
                        @if($page->images->isNotEmpty())
                            <div class="row g-2 mb-3" id="kibehoGalleryGrid">
                                @foreach($page->images as $image)
                                    <div class="col-6 col-md-4 col-lg-3 position-relative" data-image-id="{{ $image->id }}">
                                        <img src="{{ asset('storage/'.$image->image) }}" class="img-fluid rounded border" style="height:120px;width:100%;object-fit:cover;" alt="">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deleteKibehoImage({{ $image->id }})" title="Remove">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small" id="kibehoGalleryEmpty">No gallery images yet.</p>
                        @endif
                        <div class="mb-0">
                            <label class="form-label">Add gallery images</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Upload with Save page — new images are appended to the gallery.</small>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="kibeho-panel-events" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Things to do</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kibehoEventModal" onclick="resetKibehoEventForm()">
                            <i class="fa fa-plus me-1"></i> Add activity
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:90px">Image</th>
                                    <th>Title</th>
                                    <th style="width:130px">Date</th>
                                    <th>Status</th>
                                    <th style="width:120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $row)
                                <tr>
                                    <td>
                                        @if($row->image)
                                            <img src="{{ asset('storage/'.$row->image) }}" alt="" class="rounded" style="width:72px;height:48px;object-fit:cover;">
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $row->title }}</strong></td>
                                    <td>{{ $row->event_date?->format('M j, Y') ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $row->is_active ? 'success' : 'secondary' }}">{{ $row->is_active ? 'Active' : 'Hidden' }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editKibehoEvent({{ $row->id }})"><i class="fa fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteKibehoEvent({{ $row->id }})"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No activities yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="kibehoEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kibehoEventModalTitle">Add activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="kibehoEventForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="kibehoEventFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kibeho_event_title" name="title" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="kibeho_event_description" name="description" rows="4"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Event date</label>
                            <input type="date" class="form-control" id="kibeho_event_date" name="event_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort order</label>
                            <input type="number" class="form-control" id="kibeho_event_sort" name="sort_order" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">External link <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" id="kibeho_event_url" name="external_url" placeholder="https://">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="kibeho_event_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="kibeho_event_active">Show on public page</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="kibeho_event_image" name="image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
                    </div>
                    <div id="kibeho_event_image_wrap" style="display:none;">
                        <img id="kibeho_event_image_preview" src="" alt="" class="img-thumbnail" style="max-height:120px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentKibehoEventId = null;
const kibehoEventBase = @json(url('content-management/kibeho-page/events'));

document.getElementById('kibehoPageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (window.CmsSummernote) {
        CmsSummernote.syncFormData(formData, '#kibeho_description');
    }
    fetch(@json(route('content-management.kibeho-page.update')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
});

function deleteKibehoImage(id) {
    if (!confirm('Remove this image?')) return;
    fetch(@json(url('content-management/kibeho-page/images')) + '/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.querySelector('[data-image-id="' + id + '"]')?.remove();
        }
    });
}

function resetKibehoEventForm() {
    currentKibehoEventId = null;
    document.getElementById('kibehoEventForm').reset();
    document.getElementById('kibeho_event_active').checked = true;
    document.getElementById('kibehoEventModalTitle').textContent = 'Add activity';
    document.getElementById('kibeho_event_image_wrap').style.display = 'none';
}

function editKibehoEvent(id) {
    fetch(`${kibehoEventBase}/${id}`)
        .then(r => r.json())
        .then(data => {
            currentKibehoEventId = id;
            document.getElementById('kibeho_event_title').value = data.title || '';
            document.getElementById('kibeho_event_description').value = data.description || '';
            document.getElementById('kibeho_event_date').value = data.event_date ? data.event_date.substring(0, 10) : '';
            document.getElementById('kibeho_event_sort').value = data.sort_order ?? 0;
            document.getElementById('kibeho_event_url').value = data.external_url || '';
            document.getElementById('kibeho_event_active').checked = !!data.is_active;
            document.getElementById('kibeho_event_image').value = '';
            const wrap = document.getElementById('kibeho_event_image_wrap');
            const img = document.getElementById('kibeho_event_image_preview');
            if (data.image) {
                img.src = '{{ asset('storage') }}/' + data.image;
                wrap.style.display = 'block';
            } else {
                wrap.style.display = 'none';
            }
            document.getElementById('kibehoEventModalTitle').textContent = 'Edit activity';
            CmsAdmin.showModal('kibehoEventModal');
        });
}

function deleteKibehoEvent(id) {
    if (!confirm('Delete this activity?')) return;
    fetch(`${kibehoEventBase}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}

document.getElementById('kibehoEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (!document.getElementById('kibeho_event_active').checked) formData.delete('is_active');
    else formData.set('is_active', '1');
    const url = currentKibehoEventId ? `${kibehoEventBase}/${currentKibehoEventId}/update` : @json(route('content-management.kibeho-page.events.store', [], false));
    CmsAdmin.clearErrors('kibehoEventFormErrors');
    CmsAdmin.submitFormData(url, formData, {
        modalId: 'kibehoEventModal',
        errorsEl: 'kibehoEventFormErrors',
        defaultError: 'Could not save activity. Please check the form and try again.',
    });
});

</script>

@push('scripts')
<script>
jQuery(function () {
    CmsSummernote.initOnReady('#kibeho_description', {
        height: 220,
        initialHtml: @json($page->description ?? '')
    });
});
</script>
@endpush
@endsection
