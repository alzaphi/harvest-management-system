@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')

<div class="vendor-container">
    <h2>List Mandor</h2>

    <div class="vendor-header mb-4">
        <form action="{{ route('foreman.index') }}" method="GET" class="search-form" id="search-form">
            <div class="filter-group">
                <input type="text" name="search" id="search-input" 
                       placeholder="Cari nama, email, atau no HP..."
                       value="{{ request('search') }}" class="search-input">
            </div>
        </form>

        <div class="btn-group">
        @can('create-mandor')
            <a href="{{ route('foreman.create') }}" class="btn btn-primary tambah-vendor-btn">Tambah Mandor</a>
        @endcan
            <a href="{{ route('foreman.export', request()->query()) }}" class="btn btn-excel">Download Excel</a>
        </div>
    </div>

    <table class="vendor-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Mandor</th>
                <th>Nama Mandor</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Status</th>
                @can('edit-mandor')
                <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @forelse ($foremen as $index => $foreman)
                <tr>
                    <td>{{ $foremen->firstItem() + $loop->index }}</td>
                    <td>{{ $foreman->kode_mandor }}</td>
                    <td>{{ $foreman->nama_mandor }}</td>
                    <td>{{ $foreman->email }}</td>
                    <td>{{ $foreman->no_hp }}</td>
                    <td>
                        @if($foreman->status === 'Aktif')
                            <span class="status-badge status-active">
                                {{ $foreman->status }}
                            </span>
                        @elseif($foreman->status === 'Nonaktif')
                            <span class="status-badge status-inactive">
                                {{ $foreman->status }}
                            </span>
                        @else
                            <span class="status-badge status-unknown">
                                {{ $foreman->status ?? 'Unknown' }}
                            </span>
                        @endif
                    </td>
                    @can('edit-mandor')
                    <td class="text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('foreman.edit', $foreman->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                    data-delete-url="{{ route('foreman.destroy', $foreman->id) }}"
                                    data-kode-mandor="{{ $foreman->kode_mandor }}">
                                Hapus
                            </button>
                        </div>
                    </td>
                    @endcan
                </tr>
            @empty
                <tr>
                    <td colspan="{{ in_array(auth()->user()->role_name, ['Assistant Manager Plantation', 'Manager Plantation', 'Assistant Manager CDR', 'Manager CDR', 'GIS Division']) ? '6' : '7' }}" class="text-center text-gray-500">Tidak ada data mandor yang ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-500">
            Menampilkan 
            @if($foremen->count() > 0)
                {{ $foremen->firstItem() }} - {{ $foremen->lastItem() }} 
            @else
                0
            @endif
            dari {{ $foremen->total() }} data
        </div>
        <div class="ml-auto">
            {{ $foremen->appends(request()->query())->links('pagination::vendor-angkut') }}
        </div>
    </div>
</div>
@endsection

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
        const kodeMandor = button.getAttribute('data-kode-mandor');
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
                        // Show success message
                        showToast(data.message || 'Data berhasil dihapus', 'success');
                        // Reload the page after a short delay
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
    const typingInterval = 500;
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                searchForm.submit();
            }
        }, typingInterval);
    });

    searchInput.addEventListener('keydown', () => {
        clearTimeout(typingTimer);
    });

    // Close alert message
    function setupAlertCloseButtons() {
        document.querySelectorAll('.close-alert').forEach(button => {
            // Hapus event listener yang mungkin sudah ada
            button.removeEventListener('click', handleAlertClose);
            // Tambahkan event listener baru
            button.addEventListener('click', handleAlertClose);
        });
    }

    function handleAlertClose(e) {
        e.preventDefault();
        const alert = this.closest('.alert-message');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }
    }

    // Panggil fungsi setup saat dokumen siap
    document.addEventListener('DOMContentLoaded', function() {
        setupAlertCloseButtons();
    });
</script>
@endpush
