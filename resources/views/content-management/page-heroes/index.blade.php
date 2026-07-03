<div class="admin-livewire-page d-flex w-100 align-items-stretch">
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <div class="mb-4">
                <h4 class="mb-2">Page hero backgrounds</h4>
                <p class="text-muted small mb-0">
                    Set the wide banner image and title text for each public page listed below. Upload a landscape image (about 1920×600&nbsp;px). If no image is set, the page falls back to a default photo from About.
                </p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                @foreach($pageHeroes as $pageHero)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0">{{ $pageHero->page_name }}</h5>
                                @php $heroMeta = $heroDefinitions[$pageHero->page_slug] ?? null; @endphp
                                @if($heroMeta)
                                    <small class="text-primary d-block mt-1">
                                        <a href="{{ url($heroMeta['path']) }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            View {{ $heroMeta['path'] }} <i class="fa fa-external-link-alt ms-1" style="font-size:0.7em" aria-hidden="true"></i>
                                        </a>
                                    </small>
                                @endif
                            </div>
                            <span class="badge bg-{{ $pageHero->is_active ? 'success' : 'secondary' }}">
                                {{ $pageHero->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="card-body">
                            @if($pageHero->background_image)
                                <img src="{{ asset('storage/' . $pageHero->background_image) }}" 
                                     class="img-fluid rounded mb-3" 
                                     alt="{{ $pageHero->page_name }}"
                                     style="max-height: 200px; width: 100%; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded mb-3 d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fa fa-image fa-3x text-white-50"></i>
                                </div>
                            @endif
                            
                            @if($pageHero->caption)
                                <p class="text-muted mb-2"><strong>Caption:</strong> {{ $pageHero->caption }}</p>
                            @endif
                            
                            @if($pageHero->description)
                                <p class="text-muted mb-2"><small>{{ Str::limit($pageHero->description, 100) }}</small></p>
                            @endif
                            
                            <button type="button" class="btn btn-primary btn-sm w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editHeroModal{{ $pageHero->id }}">
                                <i class="fa fa-edit me-2"></i>Edit hero
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editHeroModal{{ $pageHero->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit hero — {{ $pageHero->page_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('content-management.page-heroes.update', $pageHero->id) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data"
                                  id="heroForm{{ $pageHero->id }}">
                                @csrf
                                <div class="modal-body">
                                    @php $heroMeta = $heroDefinitions[$pageHero->page_slug] ?? null; @endphp
                                    @if($heroMeta)
                                        <p class="small text-muted">
                                            This banner appears on <strong>{{ $pageHero->page_name }}</strong>
                                            (<a href="{{ url($heroMeta['path']) }}" target="_blank" rel="noopener noreferrer">{{ $heroMeta['path'] }}</a>).
                                            @if(!empty($heroMeta['note']))
                                                {{ $heroMeta['note'] }}
                                            @endif
                                        </p>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label">Background image</label>
                                        @if($pageHero->background_image)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $pageHero->background_image) }}" 
                                                     class="img-fluid rounded" 
                                                     alt="Current image"
                                                     style="max-height: 200px;">
                                            </div>
                                        @endif
                                        <input type="file" 
                                               class="form-control" 
                                               name="background_image" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        @include('content-management.includes.image-upload-hint')
                                        <small class="form-text text-muted">JPEG, PNG, GIF or WebP. Upload replaces the current image.</small>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" name="remove_background_image" id="remove_bg_{{ $pageHero->id }}" value="1">
                                        <label class="form-check-label" for="remove_bg_{{ $pageHero->id }}">Remove background image (page will use fallback photo until you upload again)</label>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Caption</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="caption" 
                                               value="{{ $pageHero->caption }}" 
                                               placeholder="Main heading on the hero">
                                        <small class="form-text text-muted">Shown as the large title over the image.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" 
                                                  name="description" 
                                                  rows="3" 
                                                  placeholder="Optional subtitle">{{ $pageHero->description }}</textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="is_active" 
                                                   value="1"
                                                   id="is_active{{ $pageHero->id }}" 
                                                   {{ $pageHero->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active{{ $pageHero->id }}">
                                                Active (use this hero when the page loads)
                                            </label>
                                        </div>
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
                @endforeach
            </div>
        </div>
    </div>
</div>
