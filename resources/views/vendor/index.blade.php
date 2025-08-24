@extends('layouts.master')

@section('title', 'List Vendor')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@php
    $header = 'List Vendor';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'List Vendor', 'url' => route('vendor.index')]
    ];
@endphp

@section('content')

<div class="vendor-container">
    <h2>List Vendor</h2>

<div class="vendor-header mb-4">
    {{-- Form Search --}}
    <form action="{{ route('vendor.index') }}" method="GET" class="search-form" id="search-form">
    
    <div class="filter-group" style="display: flex; align-items: center; gap: 10px;">
    <div style="display: flex; gap: 10px;">
        <input type="text" name="search" id="search-input" 
               class="search-input" 
               placeholder="Cari nama atau kode vendor..."
               value="{{ request('search') }}">
        
        <select name="jenis_vendor" id="jenis-vendor" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Vendor</option>
            <option value="angkut" {{ request('jenis_vendor') == 'angkut' ? 'selected' : '' }}>Vendor Angkut</option>
            <option value="tebang" {{ request('jenis_vendor') == 'tebang' ? 'selected' : '' }}>Vendor Tebang</option>
            <option value="both" {{ request('jenis_vendor') == 'both' ? 'selected' : '' }}>Vendor Angkut & Tebang</option>
        </select>

        <select name="status" id="status-filter" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>
    
    <button type="button" id="toggleTenagaKerja" class="btn btn-secondary" style="margin-left: auto;">
        Tampilkan Tenaga Kerja
    </button>
    <input type="hidden" id="vendorData" value='@json($vendors)'>
</div>
</form>

    {{-- Container tombol --}}
    <div class="btn-group">
    @can('create-vendor')
        <a href="{{ route('vendor.create') }}" class="btn btn-primary tambah-vendor-btn">Tambah Vendor</a>
    @endcan
        <a href="#" id="exportExcelBtn" class="btn btn-excel">Download Excel</a>
    </div>
</div>



    <!-- Tabel Normal -->
    <table class="vendor-table" id="normalTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Vendor</th>
                <th>Nama Vendor</th>
                <th>No Hp</th>
                <th>Keterangan</th>
                <th>Status</th>
                @if(!in_array(auth()->user()->role_name, ['mandor', 'Assistant Manager CDR', 'Manager CDR']))
                <th>No Rekening</th>
                <th>Bank</th>
                @endif
                @can('edit-vendor')
                <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @forelse ($vendors as $index => $vendor)
                <tr>
                    <td>{{ $loop->iteration + (($vendors->currentPage() - 1) * $vendors->perPage()) }}</td>
                    <td>{{ $vendor->kode_vendor }}</td>
                    <td>{{ $vendor->nama_vendor }}</td>
                    <td>{{ $vendor->no_hp }}</td>
                    <td>{{ $vendor->jenis_vendor }}</td>
                    <td>
                        @if($vendor->status === 'Aktif')
                            <span class="status-badge status-active">
                                {{ $vendor->status }}
                            </span>
                        @elseif($vendor->status === 'Nonaktif' || $vendor->status === 'Tidak Aktif')
                            <span class="status-badge status-inactive">
                                {{ $vendor->status === 'Tidak Aktif' ? 'Nonaktif' : $vendor->status }}
                            </span>
                        @else
                            <span class="status-badge status-unknown">
                                {{ $vendor->status ?? 'Unknown' }}
                            </span>
                        @endif
                    </td>
                    @if(!in_array(auth()->user()->role_name, ['mandor', 'Assistant Manager CDR', 'Manager CDR']))
                    <td>{{ $vendor->nomor_rekening }}</td>
                    <td>{{ $vendor->nama_bank }}</td>
                    @endif
                    @can('edit-vendor')
                    <td>
                        <a href="{{ route('vendor.edit', $vendor->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit"></i>Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                data-delete-url="{{ route('vendor.destroy', $vendor->id) }}"
                                data-kode-vendor="{{ $vendor->kode_vendor }}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                    @endcan
                </tr>
            @empty
                <tr>
                    <td colspan="{{ in_array(auth()->user()->role_name, ['Assistant Manager Plantation', 'Manager Plantation', 'mandor', 'Assistant Manager CDR', 'Manager CDR']) ? '7' : '9' }}" class="text-center text-gray-500">Tidak ada vendor yang ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tabel Tenaga Kerja (Awalnya Disembunyikan) -->
    <div id="tenagaKerjaContainer" style="display: none;">
        <table class="vendor-table" id="tenagaKerjaTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Vendor</th>
                    <th>Jumlah Tenaga Kerja</th>
                    @can('edit-vendor')
                    <th>Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @if(isset($tenagaKerjaVendors) && $tenagaKerjaVendors->count() > 0)
                    @foreach($tenagaKerjaVendors as $index => $vendor)
                        <tr>
                            <td>{{ $loop->iteration + (($tenagaKerjaVendors->currentPage() - 1) * $tenagaKerjaVendors->perPage()) }}</td>
                            <td>{{ $vendor->nama_vendor }}</td>
                            <td>{{ number_format($vendor->jumlah_tenaga_kerja, 0, ',', '.') }}</td>
                            @can('edit-vendor')
                            <td>
                                <a href="{{ route('vendor.edit', $vendor->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-edit"></i>Edit
                                </a>
                            </td>
                            @endcan
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ in_array(auth()->user()->role_name, ['Assistant Manager Plantation', 'Manager Plantation', 'mandor', 'Assistant Manager CDR', 'Manager CDR']) ? '2' : '3' }}" class="text-center text-gray-500">
                            Tidak ada data tenaga kerja yang ditemukan
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Pagination untuk tabel tenaga kerja -->
        @if(isset($tenagaKerjaVendors) && $tenagaKerjaVendors->hasPages())
            <div id="tenagaKerjaPagination" class="flex justify-between items-center mt-4 pagination-info">
                <div class="text-sm text-gray-500">
                    Menampilkan 
                    {{ $tenagaKerjaVendors->firstItem() ?? 0 }} - {{ $tenagaKerjaVendors->lastItem() ?? 0 }} 
                    dari {{ $tenagaKerjaVendors->total() }} data
                </div>
                <div class="ml-auto">
                    {{ $tenagaKerjaVendors->appends(request()->except('tenaga_kerja_page'))->links('pagination::vendor-angkut') }}
                </div>
            </div>
        @endif
    </div>
    
        <!-- Pagination untuk tabel normal -->
    <div id="normalPagination" class="flex justify-between items-center mt-4 pagination-info">
        <div class="text-sm text-gray-500">
            Menampilkan 
            @if($vendors->count() > 0)
                {{ $vendors->firstItem() }} - {{ $vendors->lastItem() }} 
            @else
                0
            @endif
            dari {{ $vendors->total() }} data
        </div>
        <div class="ml-auto">
            {{ $vendors->appends(array_merge(request()->except('page'), ['view' => 'normal']))->links('pagination::vendor-angkut') }}
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
        const kodeVendor = button.getAttribute('data-kode-vendor');
        const originalText = button.innerHTML;

        // Show confirmation dialog
        Swal.fire({
            title: 'Konfirmasi Hapus Permanen',
            text: `Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.`,
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
                        showToast(data.message || 'Data vendor berhasil dihapus', 'success');
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

    // Fungsi untuk memuat data tenaga kerja dengan AJAX (jika diperlukan)
    function loadTenagaKerjaData(page = 1) {
        const url = new URL(window.location);
        url.searchParams.set('tenaga_kerja_page', page);
        url.searchParams.set('ajax', '1');
        
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.querySelector('#tenagaKerjaTable tbody').innerHTML = data.html;
            }
            
            // Update pagination
            const paginationContainer = document.querySelector('.pagination');
            if (paginationContainer && data.pagination) {
                paginationContainer.innerHTML = data.pagination;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat data');
            
            const tbody = document.querySelector('#tenagaKerjaTable tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-red-500">
                            Terjadi kesalahan saat memuat data. Silakan coba lagi.
                        </td>
                    </tr>`;
            }
        });
    }
    
    // Toggle Tabel Tenaga Kerja
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleTenagaKerja');
        const normalTable = document.getElementById('normalTable');
        const tenagaKerjaContainer = document.getElementById('tenagaKerjaContainer');
        const normalPagination = document.getElementById('normalPagination');
        const tenagaKerjaPagination = document.getElementById('tenagaKerjaPagination');
        let showTenagaKerja = {{ $isTenagaKerjaPage ? 'true' : 'false' }};
        
        if (showTenagaKerja) {
            normalTable.style.display = 'none';
            tenagaKerjaContainer.style.display = 'block';
            toggleBtn.textContent = 'Tampilkan Semua Data';
            toggleBtn.classList.remove('btn-secondary');
            toggleBtn.classList.add('btn-primary');
            
            // Sembunyikan pagination normal dan tampilkan pagination tenaga kerja
            if (normalPagination) normalPagination.style.display = 'none';
            if (tenagaKerjaPagination) tenagaKerjaPagination.style.display = 'flex';
        } else {
            // Pastikan pagination normal ditampilkan saat pertama kali load
            if (normalPagination) normalPagination.style.display = 'flex';
            if (tenagaKerjaPagination) tenagaKerjaPagination.style.display = 'none';
        }
        
        // Fungsi untuk mengubah URL dengan reload halaman
        function updateUrl(viewType) {
            const url = new URL(window.location);
            
            if (viewType === 'tenaga-kerja') {
                // Hapus parameter page biasa
                url.searchParams.delete('page');
                // Set parameter view
                url.searchParams.set('view', 'tenaga-kerja');
                // Set halaman 1 jika belum ada
                if (!url.searchParams.has('tenaga_kerja_page')) {
                    url.searchParams.set('tenaga_kerja_page', '1');
                }
            } else {
                // Kembali ke view normal
                url.searchParams.delete('view');
                url.searchParams.delete('tenaga_kerja_page');
            }
            
            // Reload halaman dengan URL baru
            window.location.href = url.toString();
        }

        // Set tampilan awal
        if (showTenagaKerja) {
            // Sembunyikan tabel normal dan tampilkan tenaga kerja
            if (normalTable) normalTable.style.display = 'none';
            if (tenagaKerjaContainer) tenagaKerjaContainer.style.display = 'block';
            if (toggleBtn) {
                toggleBtn.textContent = 'Tampilkan Semua Data';
                toggleBtn.classList.remove('btn-secondary');
                toggleBtn.classList.add('btn-primary');
            }
            // Sembunyikan pagination normal
            if (normalPagination) normalPagination.style.display = 'none';
            if (tenagaKerjaPagination) tenagaKerjaPagination.style.display = 'flex';
            
            // Nonaktifkan dropdown filter saat menampilkan data tenaga kerja
            const vendorFilter = document.getElementById('jenis-vendor');
            if (vendorFilter) {
                vendorFilter.disabled = true;
            }
        } else {
            // Tampilkan tabel normal
            if (normalTable) normalTable.style.display = 'table';
            if (tenagaKerjaContainer) tenagaKerjaContainer.style.display = 'none';
            if (toggleBtn) {
                toggleBtn.textContent = 'Tampilkan Tenaga Kerja';
                toggleBtn.classList.remove('btn-primary');
                toggleBtn.classList.add('btn-secondary');
            }
            // Tampilkan pagination normal
            if (normalPagination) normalPagination.style.display = 'flex';
            if (tenagaKerjaPagination) tenagaKerjaPagination.style.display = 'none';
        }

        // Tambahkan event listener untuk tombol toggle
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const newView = showTenagaKerja ? 'normal' : 'tenaga-kerja';
                
                // Nonaktifkan/mengaktifkan dropdown filter berdasarkan tampilan
                const vendorFilter = document.getElementById('jenis-vendor');
                if (vendorFilter) {
                    vendorFilter.disabled = (newView === 'tenaga-kerja');
                }
                
                updateUrl(newView);
            });
        }
    });
    let typingTimer;
    const typingInterval = 500; // 500ms delay after typing
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');

    // Handle search input with delay
    // Handle export button click
    document.getElementById('exportExcelBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Get current URL parameters
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        
        // Check if we're in tenaga kerja view by looking at the toggle button text
        const toggleBtn = document.getElementById('toggleTenagaKerja');
        const isTenagaKerjaView = toggleBtn && toggleBtn.textContent.includes('Semua Data');
        
        // If we're in tenaga kerja view, add view=tenaga-kerja parameter
        if (isTenagaKerjaView) {
            params.set('view', 'tenaga-kerja');
        } else {
            params.delete('view');
        }
        
        // Remove pagination parameters
        params.delete('page');
        params.delete('tenaga_kerja_page');
        
        // Redirect to export URL
        window.location.href = '{{ route('vendor.export') }}?' + params.toString();
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            // Only submit if search has 3+ characters or is empty (clearing search)
            if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                searchForm.submit();
            }
        }, typingInterval);
    });

    // Clear the timer on keydown to reset the delay
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