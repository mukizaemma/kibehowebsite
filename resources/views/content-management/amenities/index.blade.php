<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')

<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div id="amenity-mgmt-config" class="d-none"
                data-url-show="{{ route('content-management.amenities.show', ['id' => '__ID__']) }}"
                data-url-store="{{ route('content-management.amenities.store') }}"
                data-url-update="{{ route('content-management.amenities.update', ['id' => '__ID__']) }}"
                data-url-destroy="{{ route('content-management.amenities.destroy', ['id' => '__ID__']) }}"
            ></div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h4 class="mb-0">Amenities Management</h4>
                <button type="button" class="btn btn-primary" data-open-add-amenity-modal>
                    <i class="fa fa-plus me-2"></i>Add New Amenity
                </button>
            </div>

            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-6 col-lg-4">
                    <label class="form-label" for="amenitySearchInput">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="search" class="form-control" id="amenitySearchInput" placeholder="Type to filter amenities…" autocomplete="off">
                    </div>
                </div>
                <div class="col-auto">
                    <small class="text-muted" id="amenitySearchCount">{{ $amenities->count() }} amenities</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="amenityTableBody">
                        @forelse($amenities as $amenity)
                        <tr data-amenity-row data-search-text="{{ $amenity->id }} {{ $amenity->title }} {{ $amenity->icon }}">
                            <td>{{ $amenity->id }}</td>
                            <td>
                                @if($amenity->icon)
                                    <i class="fa {{ $amenity->icon }} text-muted me-2" aria-hidden="true"></i>
                                @endif
                                {{ $amenity->title }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-amenity-action="edit" data-amenity-id="{{ $amenity->id }}" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-amenity-action="delete" data-amenity-id="{{ $amenity->id }}" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No amenities yet. Click &ldquo;Add New Amenity&rdquo; to create one.</td>
                        </tr>
                        @endforelse
                        <tr id="amenityNoResultsRow" class="d-none">
                            <td colspan="3" class="text-center text-muted py-4">No amenities match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Amenity Modal -->
<div class="modal fade" id="amenityModal" tabindex="-1" aria-labelledby="amenityModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amenityModalTitle">Add New Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="amenityForm" novalidate>
                <div class="modal-body">
                    <div id="amenityFormErrors" class="alert alert-danger" style="display: none;"></div>
                    <input type="hidden" id="amenity_id" name="id">
                    <div class="mb-3">
                        <label class="form-label" for="amenity_title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="amenity_title" name="title" required>
                        <div class="invalid-feedback">Please provide a title.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="amenity_icon">Icon (Font Awesome class)</label>
                        <input type="text" class="form-control" id="amenity_icon" name="icon" placeholder="fa-wifi">
                        <small class="text-muted">Optional — e.g. fa-wifi, fa-tv, fa-car</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
