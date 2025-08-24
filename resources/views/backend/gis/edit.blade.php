@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<style>
    html, body {
        height: 100%;
        margin: 0;
    }

    .map-container {
        height: 100vh;
        width: 100%;
    }

    #map {
        width: 100%;
        height: 100%;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
        background:rgb(252, 250, 248);
    }

    .leaflet-popup-content {
        font-size: 14px;
    }

    .estate-label {
        background-color: rgba(255, 255, 255, 0.8);
        color: #000;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
        border: 1px solid #999;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.8rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
        white-space: nowrap;
        line-height: 1.5;
        text-align: center;
        min-width: 70px;
    }

    .status-active {
        background-color: #D1FAE5;
        color: #065F46;
    }

    .status-inactive {
        background-color: #FEE2E2;
        color: #991B1B;
    }

    .vendor-table th:first-child,
    .vendor-table td:first-child {
        width: 1%;
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }

    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .menu-tabs-wrapper {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        margin-top: -1.5rem;
    }

    .menu-tabs {
        display: inline-flex;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        overflow: hidden;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }

    .tab-button1 {
        padding: 0.75rem 1.5rem;
        background: #f1f5f9;
        border: none;
        color: #64748b;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        position: relative;
        border-right: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        border-left: 1px solid #e2e8f0;
    }

    .tab-button1:last-child {
        border-right: none;
    }

    .tab-button1:hover {
        background: #e2e8f0;
        color: #1e40af;
    }

    .tab-button1.active {
        background: #ffffff;
        color: #1e40af;
        font-weight: 600;
        box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }

    .tab-button1.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: #1e40af;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Map Data</h2>
    </div>

    <div class="menu-tabs-wrapper">
        <div class="menu-tabs">
            <a href="{{ route('gis.index') }}" class="tab-button1 {{ request()->routeIs('gis.index') ? 'active' : '' }}">Maps View</a>
            <a href="{{ route('gis.create') }}" class="tab-button1 {{ request()->routeIs('gis.create') ? 'active' : '' }}">Maps Upload</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card p-4 mt-3">
        <form action="{{ route('gis.update', $map->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="current_file" class="form-label">Current File</label>
                <input type="text" class="form-control" value="{{ $map->file_name }}" readonly>
                <small class="text-muted">Upload a new file to replace the current one.</small>
            </div>

            <div class="mb-3">
                <label for="geojson_file" class="form-label">New GeoJSON File (Optional)</label>
                <input type="file" name="geojson_file" accept=".geojson,.json" class="form-control">
                <small class="text-muted">Leave empty to keep the current file.</small>
            </div>

            <div class="mb-3">
                <label for="uploaded_by" class="form-label">Uploaded By</label>
                <input type="text" name="uploaded_by" class="form-control" value="{{ old('uploaded_by', $map->uploaded_by) }}" required>
            </div>

            <div class="mb-3">
                <label for="estate_name" class="form-label">Estate</label>
                <input type="text" name="estate_name" class="form-control" value="{{ old('estate_name', $map->estate_name) }}">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Keterangan</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $map->description) }}</textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('gis.create') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update Data</button>
            </div>
        </form>
    </div>

    <div class="mt-4">
        <h5>File Information</h5>
        <div class="card">
            <div class="card-body">
                <p><strong>File Name:</strong> {{ $map->file_name }}</p>
                <p><strong>Uploaded On:</strong> {{ \Carbon\Carbon::parse($map->created_at)->format('d M Y H:i') }}</p>
                <p><strong>Last Updated:</strong> {{ \Carbon\Carbon::parse($map->updated_at)->format('d M Y H:i') }}</p>
                @php
                    $filePath = str_replace('public/', 'storage/', $map->file_path);
                    $fullPath = storage_path('app/public/' . str_replace('public/', '', $map->file_path));
                @endphp
                @if(file_exists($fullPath))
                    <p><strong>File Size:</strong> {{ number_format(filesize($fullPath) / 1024, 2) }} KB</p>
                    <p><strong>Download:</strong> <a href="{{ asset($filePath) }}" download>Click to download</a></p>
                @else
                    <p class="text-danger">File not found at: {{ $fullPath }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Add any additional JavaScript if needed
</script>
@endpush
@endsection
