@php
    $seoData = $seoData ?? \App\Models\SeoData::all();
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">SEO by page</h5>
            <p class="text-muted small mb-0">Meta titles, descriptions, and Open Graph data per public page.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#seoModal" onclick="resetSeoForm()">
            <i class="fa fa-plus me-2"></i>Add SEO data
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Page name</th>
                        <th>OG image</th>
                        <th>Meta title</th>
                        <th>Meta description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seoData as $seo)
                        <tr>
                            <td>{{ $seo->page_name }}</td>
                            <td>
                                @if($seo->og_image)
                                    <img src="{{ asset('storage/'.$seo->og_image) }}" alt="" class="rounded" width="72" height="54" style="object-fit: cover; display: block;" loading="lazy" decoding="async">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ $seo->meta_title }}</td>
                            <td>{{ Str::limit($seo->meta_description, 50) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editSeo({{ $seo->id }})">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No SEO records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="seoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seoModalTitle">Add SEO data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="seoForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="seo_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Page name *</label>
                        <input type="text" class="form-control" id="seo_page_name" name="page_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta title</label>
                        <input type="text" class="form-control" id="seo_meta_title" name="meta_title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta description</label>
                        <textarea class="form-control" id="seo_meta_description" name="meta_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta keywords</label>
                        <input type="text" class="form-control" id="seo_meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG title</label>
                        <input type="text" class="form-control" id="seo_og_title" name="og_title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG description</label>
                        <textarea class="form-control" id="seo_og_description" name="og_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG image</label>
                        <input type="file" class="form-control" id="seo_og_image" name="og_image" accept="image/*">
                        @include('content-management.includes.image-upload-hint')
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

@once
@push('scripts')
<script>
(function () {
    let currentSeoId = null;

    window.resetSeoForm = function () {
        currentSeoId = null;
        const form = document.getElementById('seoForm');
        if (form) {
            form.reset();
        }
        const idField = document.getElementById('seo_id');
        if (idField) {
            idField.value = '';
        }
        const title = document.getElementById('seoModalTitle');
        if (title) {
            title.textContent = 'Add SEO data';
        }
    };

    window.editSeo = function (id) {
        fetch(`/content-management/seo-data/${id}`)
            .then(response => response.json())
            .then(data => {
                currentSeoId = id;
                document.getElementById('seo_id').value = data.id;
                document.getElementById('seo_page_name').value = data.page_name;
                document.getElementById('seo_meta_title').value = data.meta_title || '';
                document.getElementById('seo_meta_description').value = data.meta_description || '';
                document.getElementById('seo_meta_keywords').value = data.meta_keywords || '';
                document.getElementById('seo_og_title').value = data.og_title || '';
                document.getElementById('seo_og_description').value = data.og_description || '';
                document.getElementById('seoModalTitle').textContent = 'Edit SEO data';
                CmsAdmin.showModal('seoModal');
            });
    };

    document.addEventListener('DOMContentLoaded', function () {
        const seoForm = document.getElementById('seoForm');
        if (!seoForm) {
            return;
        }

        seoForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const url = currentSeoId
                ? `/content-management/seo-data/update?id=${currentSeoId}`
                : '/content-management/seo-data/store';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modalElement = document.getElementById('seoModal');
                    if (modalElement) {
                        CmsAdmin.hideModal(modalElement);
                    }
                    setTimeout(() => location.reload(), 300);
                }
            });
        });
    });
})();
</script>
@endpush
@endonce
