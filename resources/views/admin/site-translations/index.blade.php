@extends('layouts.adminBase')

@section('content')
@include('content-management.includes.sidebar')
<div class="content">
    @include('admin.includes.navbar')

    <div class="container-fluid pt-4 px-4">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h5 class="mb-1">Website translations</h5>
                <p class="text-muted small mb-0">Static UI text for navigation, buttons, and labels. Leave a field empty to use the built-in default.</p>
            </div>
            @if(!$translationsEnabled)
                <span class="badge bg-warning text-dark">Disabled on public site until enabled in Settings → Translations</span>
            @elseif($missingFrench > 0)
                <span class="badge bg-secondary">{{ $missingFrench }} missing French</span>
            @endif
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <select name="group" class="form-select" onchange="this.form.submit()">
                    <option value="">All groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" @selected($group === $g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Search keys or text…">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 140px">Key</th>
                            <th>English</th>
                            <th>French</th>
                            <th style="width: 90px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php $formId = 'trans-form-'.md5($row['key']); @endphp
                            <tr>
                                <td>
                                    <div class="small text-muted">{{ $row['group'] }}</div>
                                    <code class="small">{{ $row['key'] }}</code>
                                </td>
                                <td>
                                    <input type="text" form="{{ $formId }}" name="en_value" class="form-control form-control-sm" value="{{ $row['en_override'] ? $row['en'] : '' }}" placeholder="{{ $row['en_default'] }}">
                                </td>
                                <td>
                                    <input type="text" form="{{ $formId }}" name="fr_value" class="form-control form-control-sm @if(trim($row['fr']) === '') border-warning @endif" value="{{ $row['fr_override'] ? $row['fr'] : '' }}" placeholder="{{ $row['fr_default'] ?: '—' }}">
                                </td>
                                <td>
                                    <button type="submit" form="{{ $formId }}" class="btn btn-sm btn-primary w-100">Save</button>
                                </td>
                            </tr>
                            <form id="{{ $formId }}" action="{{ route('content-management.site-translations.update') }}" method="POST" class="d-none">
                                @csrf
                                <input type="hidden" name="key" value="{{ $row['key'] }}">
                                <input type="hidden" name="group" value="{{ $group }}">
                                <input type="hidden" name="search" value="{{ $search }}">
                            </form>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No translation keys found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
