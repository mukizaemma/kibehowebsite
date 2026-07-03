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

            <div class="cms-subresource-config d-none" data-scope="kibeho-event"
                data-page-form="kibehoPageForm"
                data-page-update="{{ route('content-management.kibeho-page.update', [], false) }}"
                data-image-destroy-base="{{ str_replace('/0', '', route('content-management.kibeho-page.images.destroy', ['id' => 0], false)) }}"
                data-item-form="kibehoEventForm"
                data-item-modal="kibehoEventModal"
                data-item-title="kibehoEventModalTitle"
                data-item-active="kibeho_event_active"
                data-item-errors="kibehoEventFormErrors"
                data-item-image-wrap="kibeho_event_image_wrap"
                data-item-store="{{ route('content-management.kibeho-page.events.store', [], false) }}"
                data-item-show="{{ route('content-management.kibeho-page.events.show', ['id' => '__ID__'], false) }}"
                data-item-update="{{ route('content-management.kibeho-page.events.update', ['id' => '__ID__'], false) }}"
                data-item-destroy="{{ route('content-management.kibeho-page.events.destroy', ['id' => '__ID__'], false) }}"
                data-storage-base="{{ asset('storage') }}"
                data-summernote-field="#kibeho_description"
                data-add-label="Add activity"
            ></div>

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
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" data-cms-gallery-delete="{{ $image->id }}" data-cms-gallery-scope="kibeho-event" title="Remove">
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
                        <button type="button" class="btn btn-primary btn-sm" data-cms-subitem-action="add" data-cms-subitem-scope="kibeho-event" data-toggle="modal" data-target="#kibehoEventModal" data-bs-toggle="modal" data-bs-target="#kibehoEventModal">
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
                                        <button type="button" class="btn btn-sm btn-warning" data-cms-subitem-action="edit" data-cms-subitem-scope="kibeho-event" data-cms-subitem-id="{{ $row->id }}"><i class="fa fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" data-cms-subitem-action="delete" data-cms-subitem-scope="kibeho-event" data-cms-subitem-id="{{ $row->id }}"><i class="fa fa-trash"></i></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(function () {
    if (window.CmsSummernote) {
        CmsSummernote.initOnReady('#kibeho_description', {
            height: 220,
            initialHtml: @json($page->description ?? '')
        });
    }
});
</script>
@endpush
@endsection
