@extends('layouts.master')

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
    .filter-group {
        margin-bottom: 0;
    }
    .search-form {
        gap: 0.5rem;
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
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: all 0.15s ease-in-out;
    }
    .btn-primary {
        color: #fff;
        background-color: #0d6efd;
        border: 1px solid #0d6efd;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .btn-outline-secondary {
        color: #6c757d;
        border: 1px solid #6c757d;
        background-color: transparent;
    }
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
    }
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .action-buttons .btn i {
        margin-right: 0.25rem;
    }
    .text-center {
        text-align: center;
    }
    .text-nowrap {
        white-space: nowrap;
    }
    .text-muted {
        color: #6c757d !important;
    }
    .py-5 {
        padding-top: 3rem !important;
        padding-bottom: 3rem !important;
    }
</style>
@endpush

@section('content')
    @php
        $header = 'List Foreman Sub Block';
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            ['title' => $header]
        ];
    @endphp


    <div class="vendor-container" style="padding-bottom: 2.5rem;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">List Foreman Sub Block</h2>
            <!-- Search and other header elements can go here if needed -->
        </div>

        <!-- Menu Tabs -->
        <div class="menu-tabs-wrapper">
            <div class="menu-tabs">
                @can('view-sub-block-information')
                <a href="{{ route('sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('sub-blocks.*') ? 'active' : '' }}">Sub Block</a>
                @endcan
                <!-- @can('view-status-sub-block')
                <a href="{{ route('status-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('status-sub-blocks.*') ? 'active' : '' }}">Status Sub Block</a>
                @endcan -->
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
            <form action="{{ route('foreman-sub-blocks.index') }}" method="GET" class="d-flex align-items-center flex-grow-1" id="search-form">
                <!-- Search Input -->
                <div class="filter-group">
                    <input type="text" name="search" id="search-input" placeholder="Cari kode petak..."
                           value="{{ request('search') }}" class="form-control">
                </div>

                <!-- Foreman Name Filter -->
                <div class="ms-3 position-relative">
                    <div class="select-wrapper">
                        <select name="nama_mandor" id="nama_mandor" class="form-select" onchange="this.form.submit()" style="font-size: 0.875rem; height: 38px;" aria-label="Pilih Mandor">
                            <option value="">Semua Mandor</option>
                            @foreach($foremanNames as $name)
                                <option value="{{ $name }}" {{ request('nama_mandor') == $name ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="select-arrow">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Search and Reset Buttons -->
                <div class="ms-3">
                    <a href="{{ route('foreman-sub-blocks.index') }}" class="btn btn-outline-secondary d-flex align-items-center" title="Reset Filter" style="white-space: nowrap; font-size: 0.875rem; border: 1px solid #dee2e6;">
                        <i class="fas fa-sync-alt me-2"></i>
                        <span>Reset</span>
                    </a>
                </div>

                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('nama_mandor'))
                    <input type="hidden" name="nama_mandor" value="{{ request('nama_mandor') }}">
                @endif
            </form>

            <!-- Add New Button -->
            @can('create-foreman-sub-block')
            <div class="btn-group">
                <a href="{{ route('foreman-sub-blocks.create') }}" class="btn btn-primary tambah-vendor-btn" style="font-size: 0.85rem; font-weight: 500;">
                  Tambah Data
                </a>
            </div>
            @endcan
        </div>

        <table class="vendor-table" style="margin-top: 1rem; font-size: 0.85rem;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Petak</th>
                    <th>Kode Mandor</th>
                    <th>Nama Mandor</th>
                    <th>Divisi</th>
                    @can('edit-foreman-sub-block')
                    <th>Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($foremanSubBlocks as $index => $foreman)
                    <tr>
                        <td>{{ $foremanSubBlocks->firstItem() + $index }}</td>
                        <td>{{ $foreman->kode_petak ?? '-' }}</td>
                        <td>{{ $foreman->kode_mandor ?? '-' }}</td>
                        <td>{{ $foreman->nama_mandor ?? '-' }}</td>
                        <td>{{ $foreman->divisi ?? '-' }}</td>
                        @can('edit-foreman-sub-block')
                        <td class="text-nowrap">
                            <div class="action-buttons">
                                <a href="{{ route('foreman-sub-blocks.edit', $foreman->id) }}" class="btn btn-secondary btn-sm" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-edit"></i>Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-danger delete-btn" style="padding: 0.15rem 0.5rem; font-size: 0.85rem;"
                                    data-delete-url="{{ route('foreman-sub-blocks.destroy', $foreman->id) }}"
                                    data-kode-petak="{{ $foreman->kode_petak }}">
                                    <i class="fas fa-trash"></i>Hapus
                                </button>
                            </div>
                        </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3" style="color: #e9ecef;"></i>
                                <p class="mb-0">Tidak ada data yang tersedia</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($foremanSubBlocks->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                <div class="text-muted small">
                    Menampilkan
                    @if($foremanSubBlocks->count() > 0)
                        {{ $foremanSubBlocks->firstItem() }} - {{ $foremanSubBlocks->lastItem() }}
                    @else
                        0
                    @endif
                    dari {{ $foremanSubBlocks->total() }} data
                </div>
                <div>
                    {{ $foremanSubBlocks->onEachSide(1)->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
</div>

@push('scripts')
<script>
    // Function to update the foreman sub blocks table
    function updateForemanSubBlocksTable(html, pagination) {
        // Update the table body
        const tbody = document.querySelector('.vendor-table tbody');
        if (tbody) {
            tbody.innerHTML = html;
        }
        
        // Update the pagination
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer && pagination) {
            paginationContainer.outerHTML = pagination;
        }
        
        // Re-initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('search-form').submit();
                }
            });
        }
    });
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete button clicks with SweetAlert2 confirmation
        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-btn');
            if (!deleteBtn) return;

            e.preventDefault();
            e.stopPropagation();

            const button = deleteBtn;
            const deleteUrl = button.getAttribute('data-delete-url');
            const kodePetak = button.getAttribute('data-kode-petak');
            const originalText = button.innerHTML;

            // Show confirmation dialog
            Swal.fire({
                title: 'Konfirmasi Hapus Permanen',
                text: 'Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin menghapus data ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Permanen',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menghapus...';

                    // Get CSRF token from meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                    // Create form data
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', csrfToken);

                    // Send DELETE request using fetch API
                    fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message || 'Data berhasil dihapus',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the page to reflect changes
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Gagal menghapus data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Terjadi kesalahan saat menghapus data',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        // Reset button state
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

@endsection
