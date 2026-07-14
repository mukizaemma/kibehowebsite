<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Tour Activities</h4>
                    <p class="text-muted small mb-0">Homepage shows the first 3 active activities by display order. Manage cover image, description, and gallery photos here.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal" onclick="resetForm()">
                    <i class="fa fa-plus me-2"></i>Add New Tour Activity
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:72px">Order</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Gallery</th>
                            <th style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->sort_order }}</td>
                            <td>
                                <img src="{{ $activity->publicThumbnailUrl() }}" alt="{{ $activity->title }}" class="rounded" width="72" height="54" style="object-fit: cover; display: block;" loading="lazy" decoding="async">
                            </td>
                            <td>
                                <strong>{{ $activity->title }}</strong>
                                <div class="small text-muted">/activities/{{ $activity->slug }}</div>
                            </td>
                            <td><span class="badge bg-{{ $activity->status == 'Active' ? 'success' : 'danger' }}">{{ $activity->status }}</span></td>
                            <td>{{ $activity->images->count() }} {{ Str::plural('image', $activity->images->count()) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editActivity({{ $activity->id }})" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteActivity({{ $activity->id }})" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No activities yet. Add your first experience.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalTitle">Add New Tour Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="activityForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="activity_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" id="activity_title" name="title" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="activity_description" name="description" rows="6"></textarea>
                        <small class="text-muted">A short excerpt appears on the homepage; the full text on the activity detail page.</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select class="form-control" id="activity_status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display order</label>
                            <input type="number" class="form-control" id="activity_sort_order" name="sort_order" value="0" min="0" max="9999">
                            <small class="text-muted">Lower numbers appear first. Homepage uses the first 3 active.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Image</label>
                        <div id="activity_cover_preview" class="mb-2" style="display:none;">
                            <img id="activity_cover_preview_img" src="" alt="" class="img-fluid rounded border" style="max-height:140px;object-fit:cover;">
                        </div>
                        <input type="file" class="form-control" id="activity_cover_image" name="cover_image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gallery images</label>
                        <div id="activity_gallery_existing" class="mb-2"></div>
                        <input type="file" class="form-control" id="activity_images" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Add more photos anytime. Existing gallery images can be removed below when editing.</small>
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
let currentActivityId = null;
const activityStorageBase = @json(asset('storage'));
const activityDeleteImageUrl = @json(route('content-management.tour-activities.delete-image', ['id' => '__ID__']));

function resetForm() {
    currentActivityId = null;
    document.getElementById('activityForm').reset();
    document.getElementById('activity_id').value = '';
    document.getElementById('activity_sort_order').value = '0';
    document.getElementById('activityModalTitle').textContent = 'Add New Tour Activity';
    document.getElementById('activity_cover_preview').style.display = 'none';
    document.getElementById('activity_gallery_existing').innerHTML = '';
    if (window.CmsSummernote) {
        CmsSummernote.setCode('#activity_description', '');
    }
}

function renderActivityGallery(images) {
    const wrap = document.getElementById('activity_gallery_existing');
    if (!images || !images.length) {
        wrap.innerHTML = '<p class="text-muted small mb-0">No gallery images yet.</p>';
        return;
    }
    let html = '<div class="row g-2">';
    images.forEach(image => {
        html += `
            <div class="col-4 col-md-3 position-relative" id="activity-gallery-item-${image.id}">
                <img src="${activityStorageBase}/${image.image}" alt="" class="img-fluid rounded border" style="height:100px;width:100%;object-fit:cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deleteActivityImage(${image.id})" title="Remove">
                    <i class="fa fa-times"></i>
                </button>
            </div>`;
    });
    html += '</div>';
    wrap.innerHTML = html;
}

function editActivity(id) {
    fetch(`{{ route('content-management.tour-activities.show', ':id') }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            currentActivityId = id;
            document.getElementById('activity_id').value = data.id;
            document.getElementById('activity_title').value = data.title;
            if (window.CmsSummernote) {
                CmsSummernote.setCode('#activity_description', data.description || '');
            } else {
                document.getElementById('activity_description').value = data.description || '';
            }
            document.getElementById('activity_status').value = data.status;
            document.getElementById('activity_sort_order').value = data.sort_order ?? 0;
            document.getElementById('activityModalTitle').textContent = 'Edit Tour Activity';

            if (data.cover_image) {
                document.getElementById('activity_cover_preview_img').src = `${activityStorageBase}/${data.cover_image}`;
                document.getElementById('activity_cover_preview').style.display = 'block';
            } else {
                document.getElementById('activity_cover_preview').style.display = 'none';
            }

            renderActivityGallery(data.images || []);
            CmsAdmin.showModal('activityModal');
        });
}

function deleteActivityImage(imageId) {
    if (!confirm('Remove this gallery image?')) return;
    fetch(activityDeleteImageUrl.replace('__ID__', imageId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(`activity-gallery-item-${imageId}`);
            if (el) el.remove();
            if (!document.querySelector('#activity_gallery_existing [id^="activity-gallery-item-"]')) {
                document.getElementById('activity_gallery_existing').innerHTML = '<p class="text-muted small mb-0">No gallery images yet.</p>';
            }
        }
    });
}

function deleteActivity(id) {
    if (confirm('Are you sure you want to delete this tour activity?')) {
        fetch(`{{ route('content-management.tour-activities.destroy', ':id') }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

document.getElementById('activityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (window.CmsSummernote) {
        CmsSummernote.syncFormData(formData, '#activity_description');
    }
    const url = currentActivityId
        ? `{{ route('content-management.tour-activities.update', ':id') }}`.replace(':id', currentActivityId)
        : '{{ route('content-management.tour-activities.store') }}';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modalElement = document.getElementById('activityModal');
            if (modalElement && typeof jQuery !== 'undefined') {
                jQuery(modalElement).modal('hide');
            } else if (modalElement) {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
            setTimeout(() => location.reload(), 300);
        }
    });
});
</script>

@push('scripts')
<script>
jQuery(function () {
    CmsSummernote.initInModal('#activityModal', '#activity_description', { height: 200 });
});
</script>
@endpush
