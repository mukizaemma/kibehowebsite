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
                    <h4 class="mb-0">Pilgrimage journey</h4>
                    <p class="text-muted small mb-0">Homepage timeline: title, supporting text, background image, and steps.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#journeyStepModal" onclick="resetJourneyStepForm()">
                    <i class="fa fa-plus me-2"></i>Add step
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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
                    <h5 class="mb-1">Section content</h5>
                    <p class="text-muted small mb-3">Leave title or text empty to use the default English/French translation.</p>
                    <form action="{{ route('content-management.home-journey.intro') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="home_journey_title">Title</label>
                            <input type="text"
                                   class="form-control"
                                   id="home_journey_title"
                                   name="home_journey_title"
                                   maxlength="255"
                                   value="{{ old('home_journey_title', $setting?->home_journey_title ?? '') }}"
                                   placeholder="{{ site_trans('home.journey_title') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="home_journey_lead">Supporting text</label>
                            <textarea class="form-control"
                                      id="home_journey_lead"
                                      name="home_journey_lead"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="{{ site_trans('home.journey_lead') }}">{{ old('home_journey_lead', $setting?->home_journey_lead ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="home_journey_image">Background image</label>
                            @if(filled($setting?->home_journey_image))
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$setting->home_journey_image) }}"
                                         alt=""
                                         class="img-fluid rounded border"
                                         style="max-height:160px;object-fit:cover;width:100%;max-width:420px;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="remove_home_journey_image" name="remove_home_journey_image">
                                    <label class="form-check-label" for="remove_home_journey_image">Remove current image</label>
                                </div>
                            @endif
                            <input type="file" class="form-control" id="home_journey_image" name="home_journey_image" accept="image/*">
                            @include('content-management.includes.image-upload-hint')
                            <p class="text-muted small mt-1 mb-0">If empty, the homepage uses a Kibeho or gallery image.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Save section</button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:72px">#</th>
                            <th style="width:72px">Icon</th>
                            <th>Label</th>
                            <th style="width:90px">Order</th>
                            <th style="width:90px">Active</th>
                            <th style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($steps as $i => $step)
                        <tr>
                            <td>{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="text-center"><i class="{{ $step->icon }}"></i></td>
                            <td>
                                <strong>{{ $step->label }}</strong>
                                <div class="small text-muted">{{ $step->icon }}</div>
                            </td>
                            <td>{{ $step->sort_order }}</td>
                            <td>
                                @if($step->is_active)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editJourneyStep({{ $step->id }})" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteJourneyStep({{ $step->id }})" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No steps yet. Add the first journey step.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="journeyStepModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="journeyStepModalTitle">Add step</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="journeyStepForm">
                <div class="modal-body">
                    <input type="hidden" id="journey_step_id" name="id">
                    <div class="mb-3">
                        <label class="form-label" for="journey_step_label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="journey_step_label" name="label" required maxlength="255" placeholder="Arrive in Kigali">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="journey_step_icon">Icon class <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="journey_step_icon" name="icon" required maxlength="100" value="fa-solid fa-circle" placeholder="fa-solid fa-plane-arrival">
                        <div class="form-text">Font Awesome class, e.g. <code>fa-solid fa-church</code>, <code>fa-solid fa-bed</code>.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="journey_step_sort">Display order</label>
                        <input type="number" class="form-control" id="journey_step_sort" name="sort_order" value="0" min="0" max="9999">
                        <div class="form-text">Lower numbers appear first. Numbers 01, 02… follow this order on the site.</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="journey_step_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="journey_step_active">Show on homepage</label>
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
let currentJourneyStepId = null;
const journeyStepBaseUrl = @json(url('content-management/home-journey/steps'));

function resetJourneyStepForm() {
    currentJourneyStepId = null;
    document.getElementById('journeyStepForm').reset();
    document.getElementById('journey_step_id').value = '';
    document.getElementById('journey_step_icon').value = 'fa-solid fa-circle';
    document.getElementById('journey_step_sort').value = '0';
    document.getElementById('journey_step_active').checked = true;
    document.getElementById('journeyStepModalTitle').textContent = 'Add step';
}

function editJourneyStep(id) {
    fetch(`${journeyStepBaseUrl}/${id}`)
        .then(r => r.json())
        .then(data => {
            currentJourneyStepId = id;
            document.getElementById('journey_step_id').value = data.id;
            document.getElementById('journey_step_label').value = data.label || '';
            document.getElementById('journey_step_icon').value = data.icon || 'fa-solid fa-circle';
            document.getElementById('journey_step_sort').value = data.sort_order ?? 0;
            document.getElementById('journey_step_active').checked = !!data.is_active;
            document.getElementById('journeyStepModalTitle').textContent = 'Edit step';
            CmsAdmin.showModal('journeyStepModal');
        });
}

function deleteJourneyStep(id) {
    if (!confirm('Delete this journey step?')) return;
    fetch(`${journeyStepBaseUrl}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

document.getElementById('journeyStepForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const payload = {
        label: document.getElementById('journey_step_label').value,
        icon: document.getElementById('journey_step_icon').value,
        sort_order: parseInt(document.getElementById('journey_step_sort').value, 10) || 0,
        is_active: document.getElementById('journey_step_active').checked ? 1 : 0
    };
    const url = currentJourneyStepId
        ? `${journeyStepBaseUrl}/${currentJourneyStepId}/update`
        : @json(route('content-management.home-journey.steps.store'));

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Could not save step.');
    });
});
</script>
@endsection
