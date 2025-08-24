@extends('layouts.master')

@php
    $header = '';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')

<div class="vendor-container">
    <h2>List Kendaraan Vendor</h2>

    <div class="vendor-header mb-4">
        <form action="{{ route('vendor.vehicle.list') }}" method="GET" class="search-form" id="search-form">
            <div class="filter-group">
                <input type="text" name="search" id="search-input" 
                       placeholder="Cari kode lambung atau nomor polisi..."
                       value="{{ request('search') }}" class="search-input">
                @foreach(request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </div>
        </form>

        <div class="btn-group">
            @can('create-vehicle')
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary tambah-vendor-btn">Tambah Kendaraan</a>
            @endcan
            <a href="{{ route('vehicles.export', request()->query()) }}" class="btn btn-excel">Download Excel</a>
        </div>
    </div>

    <table class="vendor-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Vendor Angkut</th>
                <th>Nama Vendor</th>
                <th>Kode Lambung</th>
                <th>No Polisi</th>
                <th>Jenis Unit</th>
                @can('edit-vehicle')
                <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $index => $vehicle)
            <tr>
                <td>{{ $vehicles->firstItem() + $loop->index }}</td>
                <td>{{ $vehicle->kode_vendor }}</td>
                <td>{{ $vehicle->nama_vendor }}</td>
                <td>{{ $vehicle->kode_lambung }}</td>
                <td>{{ $vehicle->plat_nomor }}</td>
                <td>{{ $vehicle->jenisUnit->nama_unit ?? '-' }}</td>
                @can('edit-vehicle')
                <td>
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                data-delete-url="{{ route('vehicles.destroy', $vehicle->id) }}"
                                data-kode-lambung="{{ $vehicle->kode_lambung }}">
                            <i class="fas fa-trash"></i>Hapus
                        </button>
                    </div>
                </td>
                @endcan
            </tr>
            @empty
            <tr>
                <td colspan="{{ in_array(auth()->user()->role_name, ['Assistant Manager Plantation', 'Manager Plantation', 'mandor']) ? '6' : '7' }}" class="text-center text-gray-500 py-4">Tidak ada data kendaraan yang ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-500">
            Menampilkan 
            @if($vehicles->count() > 0)
                {{ $vehicles->firstItem() }} - {{ $vehicles->lastItem() }} 
            @else
                0
            @endif
            dari {{ $vehicles->total() }} data
        </div>
        <div class="ml-auto">
            {{ $vehicles->withQueryString()->links('pagination::vendor-angkut') }}
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Function to show toast notifications
    function showToast(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Handle delete button clicks with SweetAlert2 confirmation
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-btn');
        if (!deleteBtn) return;

        e.preventDefault();
        e.stopPropagation();

        const button = deleteBtn;
        const deleteUrl = button.getAttribute('data-delete-url');
        const kodeLambung = button.getAttribute('data-kode-lambung');
        const originalText = button.innerHTML;

        // Show confirmation dialog
        Swal.fire({
            title: 'Konfirmasi Hapus Permanen',
            text: 'Data kendaraan akan dihapus. Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin?',
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

                // Send DELETE request using fetch API
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message and reload the page
                        showToast(data.message || 'Data berhasil dihapus', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Gagal menghapus data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast(error.message || 'Terjadi kesalahan saat menghapus data', 'error');
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

    // Search functionality
    let typingTimer;
    const typingInterval = 500; // waktu tunggu setelah user selesai ngetik (0.5 detik)
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                searchForm.submit();
            }
        }, typingInterval);
    });
</script>
@endpush

@endsection
