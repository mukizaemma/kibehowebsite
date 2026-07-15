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
                    <p class="text-muted small mb-0">Manage Discover Nyaruguru, homepage teaser, activities, and gallery.</p>
                </div>
                <a href="{{ route('discover-nyaruguru') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-external-link-alt me-1"></i> View page
                </a>
            </div>

            <div class="cms-subresource-config d-none" data-scope="nyaruguru-activity"
                data-page-form="nyaruguruPageForm"
                data-page-update="{{ route('content-management.nyaruguru-page.update', [], false) }}"
                data-image-destroy-base="{{ str_replace('/0', '', route('content-management.nyaruguru-page.images.destroy', ['id' => 0], false)) }}"
                data-item-form="nyaruguruActivityForm"
                data-item-modal="nyaruguruActivityModal"
                data-item-title="nyaruguruActivityModalTitle"
                data-item-active="nyaruguru_activity_active"
                data-item-errors="nyaruguruActivityFormErrors"
                data-item-image-wrap="nyaruguru_activity_image_wrap"
                data-item-store="{{ route('content-management.nyaruguru-page.activities.store', [], false) }}"
                data-item-show="{{ route('content-management.nyaruguru-page.activities.show', ['id' => '__ID__'], false) }}"
                data-item-update="{{ route('content-management.nyaruguru-page.activities.update', ['id' => '__ID__'], false) }}"
                data-item-destroy="{{ route('content-management.nyaruguru-page.activities.destroy', ['id' => '__ID__'], false) }}"
                data-item-gallery-destroy-base="{{ str_replace('/0', '', route('content-management.nyaruguru-page.activities.images.destroy', ['id' => 0], false)) }}"
                data-storage-base="{{ asset('storage') }}"
                data-summernote-field="#nyaruguru_description"
                data-add-label="Add activity"
            ></div>

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
                                    <label class="form-label" for="nyaruguru_title">Page title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nyaruguru_title" name="title" value="{{ $page->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="nyaruguru_description">Page description</label>
                                    <textarea class="form-control" id="nyaruguru_description" name="description" rows="4"></textarea>
                                </div>
                                <hr class="my-4">
                                <h6 class="mb-3">Homepage teaser</h6>
                                <p class="text-muted small">Shown in the homepage “Why visit Nyaruguru” section. Uses the cover image on the right.</p>
                                <div class="mb-3">
                                    <label class="form-label" for="nyaruguru_home_title">Home section title</label>
                                    <input type="text" class="form-control" id="nyaruguru_home_title" name="home_title" value="{{ $page->home_title }}" maxlength="255" placeholder="Why visit Nyaruguru?">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="nyaruguru_home_lead">Home section text</label>
                                    <textarea class="form-control" id="nyaruguru_home_lead" name="home_lead" rows="4" maxlength="2000" placeholder="Short description of the beauty of Nyaruguru and the holy place of Kibeho…">{{ $page->home_lead }}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Cover image</label>
                                    <p class="text-muted small">Used on Discover Nyaruguru and the homepage teaser.</p>
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
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" data-cms-gallery-delete="{{ $image->id }}" data-cms-gallery-scope="nyaruguru-activity" title="Remove">
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
                        <button type="button" class="btn btn-primary btn-sm" data-cms-subitem-action="add" data-cms-subitem-scope="nyaruguru-activity" data-toggle="modal" data-target="#nyaruguruActivityModal" data-bs-toggle="modal" data-bs-target="#nyaruguruActivityModal">
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
                                        <button type="button" class="btn btn-sm btn-warning" data-cms-subitem-action="edit" data-cms-subitem-scope="nyaruguru-activity" data-cms-subitem-id="{{ $row->id }}"><i class="fa fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger" data-cms-subitem-action="delete" data-cms-subitem-scope="nyaruguru-activity" data-cms-subitem-id="{{ $row->id }}"><i class="fa fa-trash"></i></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nyaruguruActivityForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="nyaruguruActivityFormErrors" class="alert alert-danger d-none" role="alert"></div>
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
                        <label class="form-label">Cover image</label>
                        <input type="file" class="form-control" id="nyaruguru_activity_image" name="image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
                    </div>
                    <div id="nyaruguru_activity_image_wrap" style="display:none;">
                        <img id="nyaruguru_activity_image_preview" src="" alt="" class="img-thumbnail" style="max-height:120px;">
                    </div>
                    <div class="mb-2 mt-3">
                        <label class="form-label">Gallery images</label>
                        <div id="nyaruguru_activity_gallery_existing" class="mb-2"></div>
                        <input type="file" class="form-control" id="nyaruguru_activity_gallery" name="gallery_images[]" multiple accept="image/*">
                        <small class="text-muted">Add extra photos for the activity detail page.</small>
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
        CmsSummernote.initOnReady('#nyaruguru_description', {
            height: 220,
            initialHtml: @json($page->description ?? '')
        });
    }
});
</script>
@endpush
@endsection
