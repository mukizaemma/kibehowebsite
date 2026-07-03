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
                    <h4 class="mb-0">Nyaruguru page</h4>
                    <p class="text-muted small mb-0">Manage the public Discover Nyaruguru page — content, activities, and gallery.</p>
                </div>
                <a href="{{ route('discover-nyaruguru') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-external-link-alt me-1"></i> View page
                </a>
            </div>

            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nyaruguru-panel-page" type="button" role="tab">Page content</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nyaruguru-panel-activities" type="button" role="tab">Activities</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="nyaruguru-panel-page" role="tabpanel">
                    <form id="nyaruguruPageForm" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label" for="nyaruguru_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nyaruguru_title" name="title" value="{{ $page->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="nyaruguru_description">Description</label>
                                    <textarea class="form-control" id="nyaruguru_description" name="description" rows="4"></textarea>
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
                            <div class="row g-2 mb-3">
                                @foreach($page->images as $image)
                                    <div class="col-6 col-md-4 col-lg-3 position-relative" data-image-id="{{ $image->id }}">
                                        <img src="{{ asset('storage/'.$image->image) }}" class="img-fluid rounded border" style="height:120px;width:100%;object-fit:cover;" alt="">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deleteNyaruguruImage({{ $image->id }})" title="Remove">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small">No gallery images yet.</p>
                        @endif
                        <div class="mb-0">
                            <label class="form-label">Add gallery images</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Upload with Save page — new images are appended to the gallery.</small>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="nyaruguru-panel-activities" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Things to do</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#nyaruguruActivityModal" onclick="resetNyaruguruActivityForm()">
                            <i class="fa fa-plus me-1"></i> Add activity
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:90px">Image</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th style="width:120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $row)
                                <tr>
                                    <td>
                                        @if($row->image)
                                            <img src="{{ asset('storage/'.$row->image) }}" alt="" class="rounded" style="width:72px;height:48px;object-fit:cover;">
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $row->title }}</strong></td>
                                    <td><span class="badge bg-{{ $row->is_active ? 'success' : 'secondary' }}">{{ $row->is_active ? 'Active' : 'Hidden' }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editNyaruguruActivity({{ $row->id }})"><i class="fa fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteNyaruguruActivity({{ $row->id }})"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No activities yet.</td></tr>
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

<div class="modal fade" id="nyaruguruActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nyaruguruActivityModalTitle">Add activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="nyaruguruActivityForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nyaruguru_activity_title" name="title" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="nyaruguru_activity_description" name="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort order</label>
                        <input type="number" class="form-control" id="nyaruguru_activity_sort" name="sort_order" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External link <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" id="nyaruguru_activity_url" name="external_url" placeholder="https://">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="nyaruguru_activity_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="nyaruguru_activity_active">Show on public page</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="nyaruguru_activity_image" name="image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
                    </div>
                    <div id="nyaruguru_activity_image_wrap" style="display:none;">
                        <img id="nyaruguru_activity_image_preview" src="" alt="" class="img-thumbnail" style="max-height:120px;">
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
let currentNyaruguruActivityId = null;
const nyaruguruActivityBase = @json(url('content-management/nyaruguru-page/activities'));

document.getElementById('nyaruguruPageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (window.CmsSummernote) {
        CmsSummernote.syncFormData(formData, '#nyaruguru_description');
    }
    fetch(@json(route('content-management.nyaruguru-page.update')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
});

function deleteNyaruguruImage(id) {
    if (!confirm('Remove this image?')) return;
    fetch(@json(url('content-management/nyaruguru-page/images')) + '/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.querySelector('[data-image-id="' + id + '"]')?.remove();
        }
    });
}

function resetNyaruguruActivityForm() {
    currentNyaruguruActivityId = null;
    document.getElementById('nyaruguruActivityForm').reset();
    document.getElementById('nyaruguru_activity_active').checked = true;
    document.getElementById('nyaruguruActivityModalTitle').textContent = 'Add activity';
    document.getElementById('nyaruguru_activity_image_wrap').style.display = 'none';
}

function editNyaruguruActivity(id) {
    fetch(`${nyaruguruActivityBase}/${id}`)
        .then(r => r.json())
        .then(data => {
            currentNyaruguruActivityId = id;
            document.getElementById('nyaruguru_activity_title').value = data.title || '';
            document.getElementById('nyaruguru_activity_description').value = data.description || '';
            document.getElementById('nyaruguru_activity_sort').value = data.sort_order ?? 0;
            document.getElementById('nyaruguru_activity_url').value = data.external_url || '';
            document.getElementById('nyaruguru_activity_active').checked = !!data.is_active;
            document.getElementById('nyaruguru_activity_image').value = '';
            const wrap = document.getElementById('nyaruguru_activity_image_wrap');
            const img = document.getElementById('nyaruguru_activity_image_preview');
            if (data.image) {
                img.src = '{{ asset('storage') }}/' + data.image;
                wrap.style.display = 'block';
            } else {
                wrap.style.display = 'none';
            }
            document.getElementById('nyaruguruActivityModalTitle').textContent = 'Edit activity';
            new bootstrap.Modal(document.getElementById('nyaruguruActivityModal')).show();
        });
}

function deleteNyaruguruActivity(id) {
    if (!confirm('Delete this activity?')) return;
    fetch(`${nyaruguruActivityBase}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}

document.getElementById('nyaruguruActivityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (!document.getElementById('nyaruguru_activity_active').checked) formData.delete('is_active');
    const url = currentNyaruguruActivityId
        ? `${nyaruguruActivityBase}/${currentNyaruguruActivityId}/update`
        : @json(route('content-management.nyaruguru-page.activities.store'));
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('nyaruguruActivityModal'))?.hide();
            location.reload();
        }
    });
});
</script>

@push('scripts')
<script>
jQuery(function () {
    CmsSummernote.initOnReady('#nyaruguru_description', {
        height: 220,
        initialHtml: @json($page->description ?? '')
    });
});
</script>
@endpush
@endsection
