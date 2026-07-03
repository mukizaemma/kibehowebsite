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
                    <h4 class="mb-0">Gikongoro Diocese page</h4>
                    <p class="text-muted small mb-0">Header, profile, description, and diocese statistics.</p>
                </div>
                <a href="{{ route('discover-gikongoro-diocese') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-external-link-alt me-1"></i> View page
                </a>
            </div>

            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#gikongoro-panel-page" type="button" role="tab">Page content</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gikongoro-panel-stats" type="button" role="tab">Statistics</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="gikongoro-panel-page" role="tabpanel">
                    <form id="gikongoroPageForm" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label" for="gikongoro_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="gikongoro_title" name="title" value="{{ $page->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="gikongoro_description">Description</label>
                                    <textarea class="form-control" id="gikongoro_description" name="description" rows="6"></textarea>
                                    <small class="text-muted">Shown beside the profile image on the public page.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Header image</label>
                                    @if($page->header_image)
                                        <div class="mb-2"><img src="{{ asset('storage/'.$page->header_image) }}" alt="" class="img-fluid rounded border" style="max-height:120px;object-fit:cover;width:100%;"></div>
                                    @endif
                                    <input type="file" class="form-control" name="header_image" accept="image/*">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Profile image</label>
                                    @if($page->profile_image)
                                        <div class="mb-2"><img src="{{ asset('storage/'.$page->profile_image) }}" alt="" class="img-fluid rounded-circle border" style="width:100px;height:100px;object-fit:cover;"></div>
                                    @endif
                                    <input type="file" class="form-control" name="profile_image" accept="image/*">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Statistics background</label>
                                    @if($page->stats_background_image)
                                        <div class="mb-2"><img src="{{ asset('storage/'.$page->stats_background_image) }}" alt="" class="img-fluid rounded border" style="max-height:120px;object-fit:cover;width:100%;"></div>
                                    @endif
                                    <input type="file" class="form-control" name="stats_background_image" accept="image/*">
                                    <small class="text-muted">Background for the numbers section. Falls back to header image if empty.</small>
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
                    </form>
                </div>

                <div class="tab-pane fade" id="gikongoro-panel-stats" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Diocese statistics</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#gikongoroStatModal" onclick="resetGikongoroStatForm()">
                            <i class="fa fa-plus me-1"></i> Add item
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th style="width:120px">Value</th>
                                    <th style="width:100px">Order</th>
                                    <th>Status</th>
                                    <th style="width:120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats as $row)
                                <tr>
                                    <td>
                                        @if($row->icon)<i class="{{ $row->icon }} me-2 text-muted" aria-hidden="true"></i>@endif
                                        <strong>{{ $row->label }}</strong>
                                    </td>
                                    <td>{{ $row->value ?? '—' }}</td>
                                    <td>{{ $row->sort_order }}</td>
                                    <td><span class="badge bg-{{ $row->is_active ? 'success' : 'secondary' }}">{{ $row->is_active ? 'Active' : 'Hidden' }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editGikongoroStat({{ $row->id }})"><i class="fa fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteGikongoroStat({{ $row->id }})"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No statistics yet.</td></tr>
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

<div class="modal fade" id="gikongoroStatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gikongoroStatModalTitle">Add statistic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gikongoroStatForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gikongoro_stat_label" name="label" required maxlength="255" placeholder="e.g. Schools">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="text" class="form-control" id="gikongoro_stat_value" name="value" maxlength="50" placeholder="e.g. 42">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome)</label>
                        <input type="text" class="form-control" id="gikongoro_stat_icon" name="icon" placeholder="fa-solid fa-school">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort order</label>
                        <input type="number" class="form-control" id="gikongoro_stat_sort" name="sort_order" min="0" value="0">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="gikongoro_stat_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="gikongoro_stat_active">Show on public page</label>
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
let currentGikongoroStatId = null;
const gikongoroStatBase = @json(url('content-management/gikongoro-diocese-page/stats'));

document.getElementById('gikongoroPageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (window.CmsSummernote) {
        CmsSummernote.syncFormData(formData, '#gikongoro_description');
    }
    fetch(@json(route('content-management.gikongoro-diocese-page.update')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
});

function resetGikongoroStatForm() {
    currentGikongoroStatId = null;
    document.getElementById('gikongoroStatForm').reset();
    document.getElementById('gikongoro_stat_active').checked = true;
    document.getElementById('gikongoroStatModalTitle').textContent = 'Add statistic';
}

function editGikongoroStat(id) {
    fetch(`${gikongoroStatBase}/${id}`).then(r => r.json()).then(data => {
        currentGikongoroStatId = id;
        document.getElementById('gikongoro_stat_label').value = data.label || '';
        document.getElementById('gikongoro_stat_value').value = data.value || '';
        document.getElementById('gikongoro_stat_icon').value = data.icon || '';
        document.getElementById('gikongoro_stat_sort').value = data.sort_order ?? 0;
        document.getElementById('gikongoro_stat_active').checked = !!data.is_active;
        document.getElementById('gikongoroStatModalTitle').textContent = 'Edit statistic';
        new bootstrap.Modal(document.getElementById('gikongoroStatModal')).show();
    });
}

function deleteGikongoroStat(id) {
    if (!confirm('Delete this statistic?')) return;
    fetch(`${gikongoroStatBase}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}

document.getElementById('gikongoroStatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    if (!document.getElementById('gikongoro_stat_active').checked) formData.delete('is_active');
    const url = currentGikongoroStatId
        ? `${gikongoroStatBase}/${currentGikongoroStatId}/update`
        : @json(route('content-management.gikongoro-diocese-page.stats.store'));
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('gikongoroStatModal'))?.hide();
            location.reload();
        }
    });
});
</script>

@push('scripts')
<script>
jQuery(function () {
    CmsSummernote.initOnReady('#gikongoro_description', {
        height: 220,
        initialHtml: @json($page->description ?? '')
    });
});
</script>
@endpush
@endsection
