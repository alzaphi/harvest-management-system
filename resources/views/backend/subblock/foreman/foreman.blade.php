@extends('app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">

<style>
    .vendor-table {
        width: 100%;
        margin-top: 1rem;
    }
    .vendor-table th, .vendor-table td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    .menu-tabs {
        display: flex;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .tab-button1 {
        padding: 0.5rem 1rem;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-bottom: none;
        margin-right: 0.25rem;
        text-decoration: none;
        color: #495057;
    }
    .tab-button1.active {
        background: #fff;
        border-bottom-color: #fff;
        color: #0d6efd;
        font-weight: 500;
    }
    .vendor-container {
        padding: 1.5rem;
    }
    .vendor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .search-form {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .filter-group {
        position: relative;
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
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Foreman Sub Block Management</h1>
    </div>

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
        <h2>List Foreman Sub Block</h2>

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
            <form action="{{ route('foreman-sub-blocks.index') }}" method="GET" class="d-flex align-items-center flex-grow-1" id="search-form">
                <!-- Search Input -->
                <div class="filter-group">
                    <input type="text" name="search" id="search-input" placeholder="Cari kode petak..."
                           value="{{ request('search') }}" class="form-control">
                </div>

                <!-- Foreman Name Filter -->
                <div class="filter-group ms-3">
                    <select name="nama_mandor" id="nama_mandor" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Mandor</option>
                        @foreach($foremanNames as $name)
                            <option value="{{ $name }}" {{ request('nama_mandor') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search and Reset Buttons -->
                <div class="filter-group ms-3">

                    <a href="{{ route('foreman-sub-blocks.index') }}" class="btn btn-outline-secondary ms-2" style="height: 38px;" title="Reset Filter">
                        <i class="fas fa-sync-alt me-1"></i> Reset
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
            <div class="filter-group ms-3">
                <a href="{{ route('foreman-sub-blocks.create') }}" class="btn btn-primary d-flex align-items-center" style="height: 38px;" title="Tambah Data">
                    <i></i> Tambah Data
                </a>
            </div>
        </div>

        <table class="vendor-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Petak</th>
                    <th>Kode Petak</th>
                    <th>Kode Mandor</th>
                    <th>Nama Mandor</th>
                    <th>Divisi</th>
                    <th>Tanggal Kerja</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($foremanSubBlocks as $index => $foreman)
                    <tr>
                        <td>{{ $foremanSubBlocks->firstItem() + $index }}</td>
                        <td>{{ $foreman->subBlock->id ?? '-' }}</td>
                        <td>{{ $foreman->kode_petak ?? '-' }}</td>
                        <td>{{ $foreman->kode_mandor ?? '-' }}</td>
                        <td>{{ $foreman->nama_mandor ?? '-' }}</td>
                        <td>{{ $foreman->divisi ?? '-' }}</td>
                        <td>{{ $foreman->tanggal_kerja ? \Carbon\Carbon::parse($foreman->tanggal_kerja)->format('d/m/Y') : '-' }}</td>
                        <td class="text-nowrap">
                            <div class="action-buttons">
                                <a href="{{ route('foreman-sub-blocks.edit', $foreman->id) }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                    data-bs-toggle="tooltip"
                                    title="Hapus"
                                    data-delete-url="{{ route('foreman-sub-blocks.destroy', $foreman->id) }}"
                                    data-kode-petak="{{ $foreman->kode_petak }}">
                                    <i class="fas fa-trash"></i>Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
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
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Menampilkan {{ $foremanSubBlocks->firstItem() ?? 0 }} - {{ $foremanSubBlocks->lastItem() ?? 0 }} dari {{ $foremanSubBlocks->total() }} data
                    </div>
                    <nav aria-label="Page navigation">
                        {{ $foremanSubBlocks->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
</div>

@push('scripts')
<script>
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
