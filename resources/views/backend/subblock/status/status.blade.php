@extends('layouts.master')

@php
$header = 'List Status Sub Block';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => $header]
];
@endphp


@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">

<style>
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
    .vendor-table {
        width: 100%;
        margin-top: 1rem;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .vendor-table {
        font-size: 0.8rem;
    }
    .vendor-table th, .vendor-table td {
        padding: 0.5rem;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        word-wrap: break-word;
        white-space: normal;
    }
    .vendor-table th {
        font-size: 0.75rem;
        background-color: #f8f9fa;
    }
    .vendor-table th:first-child,
    .vendor-table td:first-child {
        width: 50px;
    }
    .vendor-table th:last-child,
    .vendor-table td:last-child {
        width: 120px;
    }
    .vendor-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin: 1rem 0;
    }
    .vendor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .search-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .filter-group {
        position: relative;
        min-width: 200px;
        margin-bottom: 0;
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
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .vendor-table {
        width: 100%;
        min-width: 1200px;
    }
    .vendor-table th {
        background-color: #f9fafb;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #4b5563;
    }
    .vendor-table td, .vendor-table th {
        padding: 1rem;
        vertical-align: middle;
        text-align: center;
    }
    .vendor-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
    }
    .vendor-table tbody tr:hover {
        background-color: #f9fafb;
    }
    .no-data {
        padding: 3rem 1rem;
        text-align: center;
        color: #6b7280;
    }
    .no-data i {
        font-size: 2.5rem;
        color: #d1d5db;
        margin-bottom: 1rem;
        display: block;
    }
    .search-input {
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        width: 300px;
    }
    .filter-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-primary {
        background-color: #0d6efd;
        color: white;
        border: 1px solid #0d6efd;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
    }
    .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }
    .alert-dismissible .btn-close {
        padding: 1rem;
    }
</style>
@endpush

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="vendor-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">List Status Sub Block</h2>
            <!-- Search and other header elements can go here if needed -->
        </div>
        <!-- Menu Tabs -->
        <div class="menu-tabs-wrapper">
            <div class="menu-tabs">
                @can('view-sub-block-information')
                <a href="{{ route('sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('sub-blocks.*') ? 'active' : '' }}">Sub Block</a>
                @endcan
                @can('view-status-sub-block')
                <a href="{{ route('status-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('status-sub-blocks.*') ? 'active' : '' }}">Status Sub Block</a>
                @endcan
                @can('view-foreman-sub-block')
                <a href="{{ route('foreman-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('foreman-sub-blocks.*') ? 'active' : '' }}">Foreman Sub Block</a>
                @endcan
                @can('view-harvest-sub-block')
                <a href="{{ route('harvest-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('harvest-sub-blocks.*') ? 'active' : '' }}">Harvest Sub Block</a>
                @endcan
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="vendor-header mb-4 d-flex align-items-center">
            <!-- Search and Filter Form -->
            <form action="{{ route('status-sub-blocks.index') }}" method="GET" class="d-flex align-items-center flex-grow-1" id="search-form">
                <!-- Search Input -->
                <div class="filter-group">
                    <input type="text" name="search" id="search-input" placeholder="Cari kode petak..."
                           value="{{ request('search') }}" class="form-control">
                </div>

                <!-- Status Filter -->
                <div class="ms-3 position-relative">
                    <div class="select-wrapper">
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()" style="font-size: 0.875rem; height: 38px;" aria-label="Pilih Status">
                            <option value="">Semua Status</option>
                            <option value="Not Yet" {{ request('status') == 'Not Yet' ? 'selected' : '' }}>Not Yet</option>
                            <option value="On Process" {{ request('status') == 'On Process' ? 'selected' : '' }}>On Process</option>
                            <option value="Done" {{ request('status') == 'Done' ? 'selected' : '' }}>Done</option>
                        </select>
                        <div class="select-arrow">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Tahun Filter -->
                <div class="ms-3 position-relative">
                    <div class="select-wrapper">
                        <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()" style="font-size: 0.875rem; height: 38px;" aria-label="Pilih Tahun">
                            <option value="">Semua Tahun</option>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        <div class="select-arrow">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Search and Reset Buttons -->
                <div class="ms-3">
                    <a href="{{ route('status-sub-blocks.index') }}" class="btn btn-outline-secondary d-flex align-items-center" title="Reset Filter" style="white-space: nowrap; font-size: 0.875rem; border: 1px solid #dee2e6;">
                        <i class="fas fa-sync-alt me-2"></i>
                        <span>Reset</span>
                    </a>
                </div>

                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('tahun'))
                    <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                @endif
            </form>

            <!-- Action Buttons -->
            @can('create-sub-block-information')
            <div class="d-flex">
                <!-- Add New Button -->
                <div class="btn-group">
                    <a href="{{ route('status-sub-blocks.create') }}" class="btn btn-primary d-flex align-items-center" style="height: 38px;" title="Tambah Status Sub Block">
                        <i></i> Tambah Data
                    </a>
                </div>

                <!-- Upload Button
                <div class="btn-group">
                    <button type="button" class="btn btn-success d-flex align-items-center" style="height: 38px;" data-bs-toggle="modal" data-bs-target="#uploadModal" title="Upload Data">
                        <i></i> Upload
                    </button>
                </div> -->
            </div>
            @endcan
        </div>


    <!-- Data Table Section -->

            <div class="table-responsive">
                <table class="vendor-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 100px;">Kode Petak</th>
                            <th style="width: 120px;">Tanggal Update</th>
                            <th style="width: 80px;">Tahun</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 80px;">Luas (ha)</th>
                            <th style="width: 80px;">Luas Status</th>
                            <th style="width: 80px;">Divisi</th>
                            <th style="width: 80px;">Aktif</th>
                            @canany(['edit-sub-block-information', 'delete-sub-block-information'])
                            <th style="width: 100px;">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedStatuses = $statusSubBlocks->groupBy('kode_petak');
                            $counter = $statusSubBlocks->firstItem();
                        @endphp

                        @forelse($groupedStatuses as $kodePetak => $statuses)
                            @php
                                $statusEntry = $statuses->first();
                                $tahunEntry = $statuses->count() > 1 ? $statuses->last() : null;
                                $rowSpan = $statuses->count() > 1 ? 2 : 1;
                            @endphp

                            <!-- Status Row -->
                            @if($statusEntry)
                                <tr>
                                    @if($loop->first || !$tahunEntry || $tahunEntry->id != $statusEntry->id)
                                        <td>{{ $counter++ }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $kodePetak }}</div>
                                            <small class="text-muted">ID: {{ $statusEntry->subBlock->id ?? '-' }}</small>
                                        </td>
                                    @endif

                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($statusEntry->tanggal_update)->isoFormat('D MMM YYYY') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($statusEntry->tanggal_update)->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ \Carbon\Carbon::parse($statusEntry->tanggal_update)->format('Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($statusEntry->status === 'Not Yet' || $statusEntry->status === 'Not Yet')
                                            <span class="badge" style="background-color: #dc3545; color: white;">Not Yet</span>
                                        @elseif($statusEntry->status === 'On Process')
                                            <span class="badge bg-warning text-dark">{{ $statusEntry->status }}</span>
                                        @elseif($statusEntry->status === 'Done')
                                            <span class="badge bg-success">{{ $statusEntry->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($statusEntry->subBlock->luas_area ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($statusEntry->luas_status, 2) }}</td>
                                    <td>{{ $statusEntry->subBlock->divisi ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge {{ $statusEntry->aktif ? 'status-active' : 'status-inactive' }}">
                                            {{ $statusEntry->aktif ? 'Aktif' : 'Non Aktif' }}
                                        </span>
                                    </td>
                                    @canany(['edit-sub-block-information', 'delete-sub-block-information'])
                                    <td class="text-nowrap">
                                        @can('edit-sub-block-information')
                                        <a href="{{ route('status-sub-blocks.edit', $statusEntry->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit"></i>Edit
                                        </a>
                                        @endcan
                                        @can('delete-sub-block-information')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                data-delete-url="{{ route('status-sub-blocks.destroy', $statusEntry->id) }}">
                                            <i class="fas fa-trash"></i>Hapus
                                        </button>
                                        @endcan
                                    </td>
                                    @endcanany
                                </tr>
                            @endif

                            <!-- Tahun Row -->
                            @if($tahunEntry && $tahunEntry->id != ($statusEntry->id ?? null))
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($tahunEntry->tanggal_update)->isoFormat('D MMM YYYY') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($tahunEntry->tanggal_update)->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            Tahun {{ \Carbon\Carbon::parse($tahunEntry->tanggal_update)->format('Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($tahunEntry->status === 'Not Yet' || $tahunEntry->status === 'Not Yet Harvested')
                                            <span class="badge" style="background-color: #dc3545; color: white;">Not Yet</span>
                                        @elseif($tahunEntry->status === 'On Process')
                                            <span class="badge" style="background-color: #ffc107; color: #000;">{{ $tahunEntry->status }}</span>
                                        @elseif($tahunEntry->status === 'Done')
                                            <span class="badge" style="background-color: #198754; color: white;">{{ $tahunEntry->status }}</span>
                                        @else
                                            {{ $tahunEntry->status }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($tahunEntry->subBlock->luas_area ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($tahunEntry->luas_status, 2) }}</td>
                                    <td>{{ $tahunEntry->subBlock->divisi ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge {{ $tahunEntry->aktif ? 'status-active' : 'status-inactive' }}">
                                            {{ $tahunEntry->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    @canany(['edit-sub-block-information', 'delete-sub-block-information'])
                                    <td class="text-nowrap">
                                        @can('edit-sub-block-information')
                                        <a href="{{ route('status-sub-blocks.edit', $tahunEntry->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete-sub-block-information')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                data-delete-url="{{ route('status-sub-blocks.destroy', $tahunEntry->id) }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </td>
                                    @endcanany
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3" style="color: #e9ecef;"></i>
                                        <h5 class="mt-3 mb-2">Tidak ada data</h5>
                                        <p class="mb-0">Tidak ada data status sub-block yang ditemukan</p>
                                        @if(request()->has('search') || request()->has('status') || request()->has('tahun'))
                                            <a href="{{ route('status-sub-blocks.index') }}" class="btn btn-outline-primary mt-3">
                                                <i class="fas fa-sync-alt me-1"></i> Reset Filter
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($statusSubBlocks->hasPages())
            <div class="card-footer bg-white border-top py-2">
                <div class="d-flex justify-content-end">
                    <style>
                        .pagination {
                            --bs-pagination-padding-x: 0.5rem;
                            --bs-pagination-padding-y: 0.2rem;
                            --bs-pagination-font-size: 0.75rem;
                            margin: 0;
                        }
                        .pagination .page-link {
                            padding: 0.25rem 0.5rem;
                            font-size: 0.75rem;
                            line-height: 1.2;
                        }
                    </style>
                    {{ $statusSubBlocks->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif

    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Data Status Sub Block</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('status-sub-blocks.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls" required>
                            <div class="form-text">Format file harus .xlsx atau .xls. <a href="{{ route('status-sub-blocks.template') }}" id="downloadTemplate" class="text-primary">Download template</a></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Penghapusan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-trash-alt fa-2x text-danger"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Hapus Data Status Sub-block</h6>
                            <p class="mb-0">Anda akan menghapus data dengan kode petak: <span id="deleteItemName" class="fw-bold"></span></p>
                            <p class="text-danger mt-2 small">
                                <i class="fas fa-exclamation-circle me-1"></i> Tindakan ini akan menghapus data secara permanen dan tidak dapat dikembalikan.
                            </p>
                        </div>
                    </div>
                    <input type="hidden" id="deleteId" name="delete_id">
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash-alt me-1"></i> Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Handle delete button clicks with SweetAlert2 confirmation
            document.addEventListener('click', async function(e) {
                const deleteBtn = e.target.closest('.delete-btn');
                if (!deleteBtn) return;

                e.preventDefault();
                const button = deleteBtn;
                const deleteUrl = button.getAttribute('data-delete-url');
                const row = button.closest('tr');

                const result = await Swal.fire({
                    title: 'Konfirmasi Hapus Permanen',
                    text: 'Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin menghapus data ini secara permanen?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });

                if (result.isConfirmed) {
                    try {
                        // Show loading state
                        const originalText = button.innerHTML;
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';

                        // Get CSRF token from meta tag
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        if (!csrfToken) {
                            throw new Error('CSRF token not found');
                        }

                        // Create form data
                        const formData = new FormData();
                        formData.append('_method', 'DELETE');
                        formData.append('_token', csrfToken);

                        // Send DELETE request using fetch API
                        const response = await fetch(deleteUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        // Check if response is JSON before parsing
                        const contentType = response.headers.get('content-type');
                        let data = {};

                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            console.error('Non-JSON response:', text);
                            throw new Error('Unexpected response format from server');
                        }

                        if (!response.ok) {
                            throw new Error(data.message || 'Gagal menghapus data');
                        }

                        // Show success message
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Data berhasil dihapus',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Remove the row from the table
                        row.remove();

                        // If no more rows in the table, reload the page
                        if (document.querySelectorAll('table tbody tr').length === 0) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: error.message || 'Terjadi kesalahan saat menghapus data',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } finally {
                        // Reset button state
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    }
                }
            });
        });
    </script>
    @endpush

    @push('scripts')
    <script>
        // Download template
        document.getElementById('downloadTemplate').addEventListener('click', function(e) {
            e.preventDefault();
            // You can replace this with the actual template download link
            window.location.href = "{{ route('status-sub-blocks.template') }}";
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle search form submission
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');
            const statusSelect = document.getElementById('status');
            const tahunSelect = document.getElementById('tahun');
            const clearSearchBtn = document.querySelector('.clear-search');

            // Function to submit form
            function submitForm() {
                if (searchForm) {
                    searchForm.submit();
                }
            }

            // Submit form when search input changes (with debounce)
            let searchTimeout;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(submitForm, 500);
                });
            }

            // Submit form when status or tahun changes
            [statusSelect, tahunSelect].forEach(select => {
                if (select) {
                    select.addEventListener('change', submitForm);
                }
            });

            // Clear search input
            if (clearSearchBtn && searchInput) {
                clearSearchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchInput.value = '';
                    submitForm();
                });
            }
        }); // Close the DOMContentLoaded event listener
    </script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    @endpush


</div>

@endsection
