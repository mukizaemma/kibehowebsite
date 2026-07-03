@extends('layouts.adminBase')

@section('content')
<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Rooms Management</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="resetForm()">
                    <i class="fa fa-plus me-2"></i>Add New Room
                </button>
            </div>

            <!-- Rooms tab -->
            <ul class="nav nav-tabs mb-3" id="roomsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms-list" type="button" role="tab" aria-controls="rooms-list" aria-selected="true">
                        Rooms
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="roomsTabsContent">
                <!-- Rooms table -->
                <div class="tab-pane fade show active" id="rooms-list" role="tabpanel" aria-labelledby="rooms-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cover</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Gallery</th>
                                    <th>Amenities</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rooms->where('room_type', 'room') as $room)
                                <tr>
                                    <td>{{ $room->id }}</td>
                                    <td>
                                        <img
                                            src="{{ $room->publicThumbnailUrl() }}"
                                            alt="{{ $room->title }}"
                                            class="rounded"
                                            width="72"
                                            height="54"
                                            style="object-fit: cover; display: block;"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </td>
                                    <td>{{ $room->title }}</td>
                                    <td><span class="badge bg-{{ $room->status == 'Active' ? 'success' : 'danger' }}">{{ $room->status }}</span></td>
                                    <td>{{ hotel_price($room->price ?? 0, $setting) }}<span class="text-muted">/night</span></td>
                                    <td>{{ $room->images->count() }} {{ Str::plural('image', $room->images->count()) }}</td>
                                    <td>{{ $room->amenities->count() }} amenities</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewRoom({{ $room->id }})">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editRoom({{ $room->id }})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteRoom({{ $room->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalTitle">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="roomForm" enctype="multipart/form-data" novalidate>
                <div class="modal-body">
                    <div id="roomFormErrors" class="alert alert-danger" style="display: none;"></div>
                    <input type="hidden" id="room_id" name="id">
                    <p class="text-muted small mb-3">
                        Inventory and detailed rates are managed in your online booking channel. Use this form for the room name, marketing description, and indicative price shown on the website.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="room_title" name="title" required>
                        <div class="invalid-feedback">Please provide a title.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ({{ price_currency_label($setting) }} / night) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="room_price" name="price" min="0" step="1" required style="max-width: 220px;">
                        <small class="text-muted">Indicative rate displayed on the website. Live availability and pricing come from the booking channel.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="room_description" name="description" rows="6" placeholder="Enter room description..."></textarea>
                        <small class="text-muted">Use the rich text editor toolbar to format your room description with styles, colors, lists, and more</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Image</label>
                        <input type="file" class="form-control" id="room_cover_image" name="cover_image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
                        <small class="text-muted">Optional - Main image for the room</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gallery Images (Multiple)</label>
                        <input type="file" class="form-control" id="room_images" name="images[]" multiple accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amenities</label>
                        <div class="row" style="max-height: 200px; overflow-y: auto;">
                            @foreach($amenities as $amenity)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity_{{ $amenity->id }}">
                                    <label class="form-check-label" for="amenity_{{ $amenity->id }}">
                                        {{ $amenity->title }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="room_status" name="status" required>
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback">Please select a status.</div>
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

<script>
@php
    $adminCurrencyRwf = strtolower((string) (optional($setting)->price_currency ?? 'usd')) === 'rwf';
@endphp
const __roomAdminPriceIsRwf = @json($adminCurrencyRwf);
function __formatRoomAdminPrice (v) {
    if (v == null || v === '' || typeof v === 'undefined') { return '—'; }
    const n = Number(v);
    if (Number.isNaN(n)) { return '—'; }
    if (__roomAdminPriceIsRwf) {
        return 'RWF ' + n.toLocaleString('en-US', { maximumFractionDigits: 0 }) + ' / night';
    }
    return '$' + n.toLocaleString('en-US', { maximumFractionDigits: 0 }) + ' / night';
}
let currentRoomId = null;

function resetForm() {
    currentRoomId = null;
    const form = document.getElementById('roomForm');
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('room_id').value = '';
    document.getElementById('roomModalTitle').textContent = 'Add New Room';
    document.getElementById('roomFormErrors').style.display = 'none';
    document.getElementById('roomFormErrors').innerHTML = '';
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('input[name="amenities[]"]').forEach(cb => cb.checked = false);
    document.getElementById('room_status').value = 'Active';
    // Reset Summernote
    if (window.CmsSummernote) {
        CmsSummernote.setCode('#room_description', '');
    }
}

function editRoom(id) {
    fetch(`{{ route('content-management.rooms.show', ':id') }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            currentRoomId = id;
            document.getElementById('room_id').value = data.id;
            document.getElementById('room_title').value = data.title;
            // Set Summernote content properly
            if (window.CmsSummernote) {
                CmsSummernote.setCode('#room_description', data.description || '');
            }
            document.getElementById('room_price').value = data.price ?? '';
            document.getElementById('room_status').value = data.status || 'Active';
            
            // Set amenities
            document.querySelectorAll('input[name="amenities[]"]').forEach(cb => cb.checked = false);
            if (data.amenities) {
                data.amenities.forEach(amenity => {
                    const checkbox = document.getElementById(`amenity_${amenity.id}`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            document.getElementById('roomModalTitle').textContent = 'Edit Room';
            CmsAdmin.showModal('roomModal');
        });
}

function deleteRoom(id) {
    if (confirm('Are you sure you want to delete this room?')) {
        fetch(`{{ route('content-management.rooms.destroy', ':id') }}`.replace(':id', id), {
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

document.getElementById('roomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        form.querySelectorAll(':invalid').forEach(field => {
            field.classList.add('is-invalid');
        });
        return false;
    }
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    const formData = new FormData(form);
    if (window.CmsSummernote) {
        CmsSummernote.syncFormData(formData, '#room_description');
    }
    const url = currentRoomId 
        ? `{{ route('content-management.rooms.update', ':id') }}`.replace(':id', currentRoomId)
        : '{{ route('content-management.rooms.store') }}';
    
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
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        
        if (data.success) {
            CmsAdmin.hideModal('roomModal');
            setTimeout(() => location.reload(), 300);
        } else {
            const errorDiv = document.getElementById('roomFormErrors');
            errorDiv.style.display = 'block';
            let errorHtml = '<strong>Please fix the following errors:</strong><ul class="mb-0">';
            
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    errorHtml += `<li>${data.errors[field][0]}</li>`;
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            } else if (data.message) {
                errorHtml += `<li>${data.message}</li>`;
            }
            errorHtml += '</ul>';
            errorDiv.innerHTML = errorHtml;
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        const errorDiv = document.getElementById('roomFormErrors');
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = `<strong>Error:</strong> ${error.message || 'An error occurred. Please try again.'}`;
    });
});

function viewRoom(id) {
    fetch(`{{ route('content-management.rooms.show', ':id') }}`.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            // Display room details
            document.getElementById('viewRoomTitle').textContent = data.title;
            document.getElementById('viewRoomDetails').innerHTML = `
                <p><strong>Price:</strong> ${data.price != null && data.price !== '' ? __formatRoomAdminPrice(data.price) : 'N/A'}</p>
                <p><strong>Status:</strong> <span class="badge bg-${data.status == 'Active' ? 'success' : 'danger'}">${data.status}</span></p>
                <p><strong>Description:</strong> ${data.description || 'N/A'}</p>
            `;
            
            // Display cover image
            const coverImageHtml = data.cover_image 
                ? `<img src="{{ asset('storage/') }}/${data.cover_image}" class="img-fluid rounded mb-3" style="max-height: 300px; width: 100%; object-fit: cover;">`
                : '<p class="text-muted">No cover image</p>';
            document.getElementById('viewRoomCoverImage').innerHTML = coverImageHtml;
            
            // Display gallery images
            let galleryHtml = '';
            if (data.images && data.images.length > 0) {
                galleryHtml = '<div class="row g-2">';
                data.images.forEach(image => {
                    galleryHtml += `
                        <div class="col-md-3 position-relative">
                            <img src="{{ asset('storage/') }}/${image.image}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="deleteRoomImage(${image.id}, ${id})" style="z-index: 10;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    `;
                });
                galleryHtml += '</div>';
            } else {
                galleryHtml = '<p class="text-muted">No gallery images</p>';
            }
            document.getElementById('viewRoomGallery').innerHTML = galleryHtml;
            
            // Set room ID for adding images
            document.getElementById('addRoomImagesRoomId').value = id;
            
            CmsAdmin.showModal('viewRoomModal');
        });
}

function deleteRoomImage(imageId, roomId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`{{ route('content-management.rooms.delete-image', ':id') }}`.replace(':id', imageId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                viewRoom(roomId); // Refresh view
            }
        });
    }
}

function addRoomImages() {
    const form = document.getElementById('addRoomImagesForm');
    const formData = new FormData(form);
    const roomId = formData.get('room_id');
    
    fetch(`{{ route('content-management.rooms.add-images', ':id') }}`.replace(':id', roomId), {
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
            document.getElementById('addRoomImagesForm').reset();
            viewRoom(roomId); // Refresh view
        }
    });
}

</script>

@push('scripts')
<script>
jQuery(function () {
    var roomEditorOptions = {
        height: 300,
        minHeight: 200,
        maxHeight: 500,
        focus: false,
        placeholder: 'Enter room description...',
        dialogsFade: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'Gilda Display', 'Jost'],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
        callbacks: {
            onInit: function () {
                jQuery('.note-editor').css({
                    border: '1px solid #ced4da',
                    'border-radius': '0.375rem'
                });
            }
        }
    };
    CmsSummernote.initInModal('#roomModal', '#room_description', roomEditorOptions);
});
</script>
@endpush

<!-- View Room Modal -->
<div class="modal fade" id="viewRoomModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRoomTitle">Room Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="viewRoomDetails"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Cover Image</h6>
                        <div id="viewRoomCoverImage"></div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Gallery Images</h6>
                    <div id="viewRoomGallery"></div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Add More Gallery Images</h6>
                    <form id="addRoomImagesForm" onsubmit="event.preventDefault(); addRoomImages();">
                        <input type="hidden" id="addRoomImagesRoomId" name="room_id">
                        <input type="file" class="form-control mb-2" name="images[]" multiple accept="image/*" required>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add Images
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
