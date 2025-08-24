@extends('layouts.master')

@php
    $header = 'Lembar Kerja Tebang';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Lembar Kerja Tebang', 'url' => route('lkt.index')]
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link rel="stylesheet" href="{{ asset('css/spt.css') }}">
<style>
    /* Make table cells wrap text */
    .vendor-table td {
        white-space: normal !important;
        word-wrap: break-word;
    }
    
    /* Status styles */
    .status-waiting {
        background-color: #f8fafc !important;
        color: #64748b !important;
        border-color: #e2e8f0 !important;
    }
    
    .filter-container {
        position: relative;
        display: inline-block;
    }
    .filter-panel {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.5rem;
        z-index: 50;
        width: 32rem;
        background: white;
        border: 1px solid #ddd;
        border-radius: 0.5rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        padding: 1.5rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    @media (max-width: 768px) {
        .filter-panel {
            width: 90vw;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
        }
        .filter-panel.show {
            transform: translateX(-50%) translateY(0);
        }
    }
    .filter-panel.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .vendor-header {
        position: relative;
        overflow: visible;
    }
    #filter-toggle {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('page-title', 'Lembar Kerja Tebang (LKT)')

@section('content')
@if(session('success'))
    <div id="success-message" data-message="{{ session('success') }}"></div>
@endif
@if(session('error'))
    <div id="error-message" data-message="{{ session('error') }}"></div>
@endif

<div class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<div class="vendor-container">
    <h2>Lembar Kerja Tebang (LKT)</h2>

    <div class="vendor-header mb-4">
        <div class="flex items-center space-x-4">
            <form action="{{ route('lkt.index') }}" method="GET" class="search-form flex-grow" id="search-form">
                <div class="filter-group relative">
                    <input type="text" name="search" id="search-input"
                           class="search-input w-full"
                           placeholder="Cari kode LKT, vendor, atau driver..."
                           value="{{ request('search') }}">
                    <input type="hidden" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                    <input type="hidden" name="tanggal_selesai" id="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                    <input type="hidden" name="status" id="filter_status" value="{{ request('status') }}">
                </div>
            </form>

            <div class="filter-container">
                <button type="button" id="filter-toggle" class="btn btn-secondary flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Filter
                </button>
                <!-- Filter Panel -->
                <div id="filter-panel" class="filter-panel">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Tanggal Tebang -->
                        <div class="col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tebang</label>
                            <div class="flex space-x-2">
                                <input type="date" id="tanggal_mulai_input"
                                       class="form-input w-full rounded-md shadow-sm"
                                       value="{{ request('tanggal_mulai') }}">
                                <span class="flex items-center">s/d</span>
                                <input type="date" id="tanggal_selesai_input"
                                       class="form-input w-full rounded-md shadow-sm"
                                       value="{{ request('tanggal_selesai') }}">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-span-2">
                            <label for="status_select" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status_select" class="form-select w-full rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                @if(auth()->user()->role_name === 'PT PAG')
                                    <option value="Waiting" {{ request('status') == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                @else
                                    @if(auth()->user()->role_name == 'admin' || auth()->user()->role_name == 'mandor')
                                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    @endif
                                    <option value="Diajukan" {{ request('status') == 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                                    <option value="Waiting" {{ request('status') == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                                    <option value="Diperiksa" {{ request('status') == 'Diperiksa' ? 'selected' : '' }}>Diperiksa</option>
                                    <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                @endif
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-span-3 flex justify-end space-x-2 pt-2">
                            <button type="button" id="reset-filter" class="btn btn-secondary">Reset</button>
                            <button type="button" id="apply-filter" class="btn btn-primary">Terapkan Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="btn-group mt-4">
            @if(in_array(auth()->user()->role_name, ['admin', 'mandor']))
                <a href="{{ route('lkt.create') }}" class="btn btn-primary">Tambah LKT</a>
            @endif
            <a href="{{ route('lkt.export', request()->query()) }}" class="btn btn-excel"><i class="fas fa-file-excel"></i> Download Excel</a>
        </div>
    </div>

    <div class="mt-6">
        <div id="table-container">
        <div class="overflow-x-auto">
            <table class="vendor-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode LKT</th>
                        <th>Kode SPT</th>
                        <th class="whitespace-normal leading-tight" style="min-width: 80px;">Tanggal<br>Tebang</th>
                        <th>Vendor Tebang</th>
                        <th>Vendor Angkut</th>
                        <th>Diawasi Oleh</th>
                        <th>Driver</th>
                        <th>Kode Petak</th>
                        <th>Zona</th>
                        <th class="whitespace-normal leading-tight">Jenis Tebangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lkts as $index => $lkt)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lkt->kode_lkt }}</td>
                        <td>{{ $lkt->kode_spt }}</td>
                        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($lkt->tanggal_tebang)->format('d/m/Y') }}</td>
                        <td>
                            @if($lkt->vendorTebang)
                                <div class="font-medium">{{ $lkt->vendorTebang->nama_vendor }}</div>
                                <div class="text-xs text-gray-500">{{ $lkt->vendorTebang->kode_vendor }}</div>
                            @else
                                <div class="text-xs text-gray-500">-</div>
                            @endif
                        </td>
                        <td>
                            @if($lkt->vendorAngkut)
                                <div class="font-medium">{{ $lkt->vendorAngkut->nama_vendor }}</div>
                                <div class="text-xs text-gray-500">{{ $lkt->vendorAngkut->kode_vendor }}</div>
                            @else
                                <div class="text-xs text-gray-500">-</div>
                            @endif
                        </td>
                        <td>
                            @if($lkt->spt && $lkt->spt->foremanSubBlock)
                                <div class="font-medium">{{ $lkt->spt->foremanSubBlock->nama_mandor }}</div>
                                <div class="text-xs text-gray-500">{{ $lkt->spt->foremanSubBlock->kode_mandor }}</div>
                            @else
                                <div class="text-xs text-gray-500">-</div>
                            @endif
                        </td>
                        <td>
                            @if($lkt->driver)
                                {{ $lkt->driver->nama_vendor }}<br>
                                {{ $lkt->driver->kode_lambung }} / {{ $lkt->driver->plat_nomor }}
                            @else - @endif
                        </td>
                        <td>{{ $lkt->kode_petak }}</td>
                        <td>{{ $lkt->tarif_zona_angkutan }}</td>
                        <td class="whitespace-nowrap">{{ $lkt->jenis_tebangan }}</td>

                        <td>
                            @php
                                $displayStatus = $lkt->status_label ?? 'Draft';
                                $statusClass = [
                                    'Disetujui' => 'status-active',
                                    'Diperiksa' => 'status-pending',
                                    'Diajukan' => 'status-submitted',
                                    'Draft' => 'status-draft',
                                    'Selesai' => 'status-completed',
                                    'Waiting' => 'status-waiting'
                                ][$displayStatus] ?? 'status-draft';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="flex items-center" style="justify-content: center;">
                                <a href="{{ route('lkt.show', $lkt->id) }}" class="btn btn-secondary btn-sm mr-2">Lihat</a>
                                @if(in_array(auth()->user()->role_name, ['admin', 'mandor']))
                                    @if($lkt->status !== 'Draft')
                                        <button type="button" class="btn btn-secondary btn-sm"
                                           onclick="event.preventDefault();
                                           Swal.fire({
                                               icon: 'warning',
                                               title: 'Tidak Dapat Mengedit',
                                               text: 'LKT dengan status {{ $lkt->status }} tidak dapat diedit karena sudah diajukan.',
                                               confirmButtonText: 'Mengerti'
                                           });">EDIT</button>
                                    @else
                                        <a href="{{ route('lkt.edit', $lkt->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    @endif
                                    <form id="delete-form-{{ $lkt->id }}" action="{{ route('lkt.destroy', $lkt->id) }}" method="POST" class="d-inline ml-2">
                                        @csrf
                                        @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $lkt->id }}">Hapus</button>
                                </form>
                                @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-gray-500">Tidak ada data yang ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lkts->hasPages())
        <div class="flex justify-between items-center mt-2">
            <div class="text-sm text-gray-500">
                Menampilkan
                @if($lkts->count() > 0)
                    {{ $lkts->firstItem() }} - {{ $lkts->lastItem() }}
                @else
                    0
                @endif
                dari {{ $lkts->total() }} data
            </div>
            <div class="ml-auto">
                {{ $lkts->appends(request()->query())->links('pagination::vendor-angkut') }}
            </div>
        </div>
        @endif
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            const lktId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus LKT ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    // Toggle filter panel
    const filterToggle = document.getElementById('filter-toggle');
    const filterPanel = document.getElementById('filter-panel');
    const loadingOverlay = document.querySelector('.loading-overlay');
    
    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function() {
            filterPanel.classList.toggle('show');
        });
        
        // Close filter panel when clicking outside
        document.addEventListener('click', function(event) {
            if (!filterPanel.contains(event.target) && event.target !== filterToggle) {
                filterPanel.classList.remove('show');
            }
        });
    }
    
    // Show loading overlay
    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }
    }
    
    // Hide loading overlay
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }
    
    // Handle search input with debounce
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateFilters();
            }, 500);
        });
    }
    
    // Handle filter form
    const applyFilterBtn = document.getElementById('apply-filter');
    const resetFilterBtn = document.getElementById('reset-filter');
    const tanggalMulaiInput = document.getElementById('tanggal_mulai_input');
    const tanggalSelesaiInput = document.getElementById('tanggal_selesai_input');
    const statusSelect = document.getElementById('status_select');
    const tanggalMulaiHidden = document.getElementById('tanggal_mulai');
    const tanggalSelesaiHidden = document.getElementById('tanggal_selesai');
    const statusHidden = document.getElementById('filter_status');
    
    // Apply filter
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            tanggalMulaiHidden.value = tanggalMulaiInput.value;
            tanggalSelesaiHidden.value = tanggalSelesaiInput.value;
            statusHidden.value = statusSelect.value;
            updateFilters();
            filterPanel.classList.remove('show');
        });
    }
    
    // Reset filter
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            tanggalMulaiInput.value = '';
            tanggalSelesaiInput.value = '';
            statusSelect.value = '';
            tanggalMulaiHidden.value = '';
            tanggalSelesaiHidden.value = '';
            statusHidden.value = '';
            searchInput.value = '';
            updateFilters();
        });
    }
    
    // Function to update filters and fetch results
    function updateFilters() {
        showLoading();
        
        // Submit the form normally to refresh the page with new filters
        searchForm.submit();
    }
    
    // Initialize date inputs with current values
    if (tanggalMulaiInput && tanggalMulaiHidden.value) {
        tanggalMulaiInput.value = tanggalMulaiHidden.value;
    }
    if (tanggalSelesaiInput && tanggalSelesaiHidden.value) {
        tanggalSelesaiInput.value = tanggalSelesaiHidden.value;
    }
    
    // Show success/error messages using SweetAlert2
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    
    if (successMessage) {
        const message = successMessage.getAttribute('data-message');
        if (message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }
    
    if (errorMessage) {
        const message = errorMessage.getAttribute('data-message');
        if (message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }
});
</script>
@endpush
@endsection
