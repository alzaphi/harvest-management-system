@extends('layouts.master')

@php
    $header = 'Surat Perintah Tebang (SPT)';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Surat Perintah Tebang (SPT)', 'url' => route('spt.index')]
    ];
@endphp

@section('page-title', $header)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link rel="stylesheet" href="{{ asset('css/spt.css') }}">
<style>
    .filter-container {
        position: relative;
        display: inline-block;
    }
    .filter-panel {
        position: absolute;
        top: 0;
        left: 100%; /* muncul ke kanan */
        margin-left: 1rem; /* jarak dari tombol */
        z-index: 50;
        width: 36rem;
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
    .filter-panel.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
<!-- Alert -->
@if(session('success'))
    <div id="success-message" data-message="{{ session('success') }}"></div>
@endif

@if(session('error'))
    <div id="error-message" data-message="{{ session('error') }}"></div>
@endif

<div class="vendor-container">
    <h2>Surat Perintah Tebang (SPT)</h2>

    <div class="vendor-header mb-4">
        <div class="flex items-center space-x-4">
            <form action="{{ route('spt.index') }}" method="GET" class="search-form flex-grow" id="search-form">
                <div class="filter-group relative">
                    <input type="text" name="search" id="search-input"
                           class="search-input w-full"
                           placeholder="Cari Nomor SPT, vendor, atau jenis tebang..."
                           value="{{ request('search') }}">
                    <input type="hidden" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                    <input type="hidden" name="tanggal_selesai" id="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
                    <input type="hidden" name="jenis_tebang" id="filter_jenis_tebang" value="{{ request('jenis_tebang') }}">
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
                    <!-- Pindahkan isi filter panel ke sini -->
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

                        <!-- Jenis Tebang -->
                        <div class="col-span-2">
                            <label for="jenis_tebang_select" class="block text-sm font-medium text-gray-700 mb-1">Jenis Tebang</label>
                            <select id="jenis_tebang_select" class="form-select w-full rounded-md shadow-sm">
                                <option value="">Semua Jenis</option>
                                <option value="Manual" {{ request('jenis_tebang') == 'Manual' ? 'selected' : '' }}>Manual</option>
                                <option value="Semi-Mekanis" {{ request('jenis_tebang') == 'Semi-Mekanis' ? 'selected' : '' }}>Semi-Mekanis</option>
                                <option value="Mekanis" {{ request('jenis_tebang') == 'Mekanis' ? 'selected' : '' }}>Mekanis</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status_select" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status_select" class="form-select w-full rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                @if(auth()->user()->role_name !== 'Assistant Manager Plantation')
                                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Diajukan" {{ request('status') == 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                                @endif
                                <option value="Waiting" {{ request('status') == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="Diperiksa" {{ request('status') == 'Diperiksa' ? 'selected' : '' }}>Diperiksa</option>
                                <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-span-3 flex justify-end space-x-2 pt-2">
                            <button type="button" id="reset-filter" class="btn btn-secondary px-4 py-2">
                                Reset
                            </button>
                            <button type="button" id="apply-filter" class="btn btn-primary px-4 py-2">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-group mt-4">
        @if(in_array(auth()->user()->role_name, ['admin', 'Assistant Divisi Plantation']))
            <a href="{{ route('spt.create') }}" class="btn btn-primary tambah-vendor-btn">Tambah SPT</a>
@endif
            <a href="{{ route('spt.export', request()->query()) }}" class="btn btn-excel">Download Excel</a>
        </div>
    </div>

    <div style="margin-top: 1rem;">
    <table class="vendor-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor SPT</th>
                <th>Vendor Tebang</th>
                <th>Kode Petak</th>
                <th>Estate</th>
                <th>Divisi</th>
                <th>Luas Area (Ha)</th>
                <th>Zona</th>
                <th>Diawasi Oleh</th>
                <th>Tanggal Tebang</th>
                <th>Jumlah Tenaga</th>
                <th>Jenis Tebang</th>
                <th>Status</th>

                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($spts as $index => $spt)
                <tr>
                    <td>{{ $spts->firstItem() + $loop->index }}</td>
                    <td class="text-center">{{ $spt->kode_spt }}</td>
                    <td>
                        @if($spt->vendor)
                            <div class="font-medium">{{ $spt->vendor->nama_vendor }}</div>
                            <div class="text-xs text-gray-500">{{ $spt->kode_vendor }}</div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </td>
                    <td>{{ $spt->kode_petak }}</td>
                    <td>{{ $spt->estate ?? '-' }}</td>
                    <td>{{ $spt->divisi ?? '-' }}</td>
                    <td>{{ $spt->luas_area ? number_format($spt->luas_area, 2) : '-' }}</td>
                    <td>{{ $spt->zona ?? '-' }}</td>
                    <td>
                        @if($spt->foremanSubBlock)
                            <div class="font-medium">{{ $spt->foremanSubBlock->nama_mandor }}</div>
                            <div class="text-xs text-gray-500">{{ $spt->foremanSubBlock->kode_mandor }}</div>
                        @else
                            <div class="text-xs text-gray-500">-</div>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($spt->tanggal_tebang)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $spt->jumlah_tenaga_kerja }}</td>
                    <td>{{ $spt->jenis_tebang }}</td>
                    <td>
                        @php
                            $displayStatus = $spt->display_status ?? $spt->status ?? 'Draft';
                            $statusClass = [
                                'Disetujui' => 'status-active',
                                'Diperiksa' => 'status-pending',
                                'Diajukan' => 'status-submitted',
                                'Waiting' => 'status-warning',
                                'Draft' => 'status-draft'
                            ][$displayStatus] ?? 'status-draft';
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $displayStatus }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <div class="flex items-center" style="justify-content: center;">
                            <a href="{{ route('spt.show', $spt->id) }}" class="btn btn-secondary btn-sm mr-2">Lihat</a>
                            
                            @php
                                // Debug info
                                $user = auth()->user();
                                $userId = $user->id;
                                $sptId = $spt->id;
                                
                                // Tampilkan info user di console log
                                echo "<script>console.log('User ID: $userId, Name: {$user->name}, Role: {$user->role_name}');</script>";
                                
                                // Cek konfirmasi dengan query langsung
                                $confirmation = \App\Models\SptConfirmation::where('spt_id', $sptId)
                                    ->where('user_id', $userId)
                                    ->whereIn('role_name', ['mandor', 'vendor'])
                                    ->first();
                                
                                $isConfirmed = !is_null($confirmation);
                                $confirmedAt = $isConfirmed ? $confirmation->created_at->format('d/m/Y H:i') : '';
                                
                                // Tampilkan debug info di layar (akan dihapus nanti)
                                $debugInfo = "SPT ID: $sptId, User ID: $userId, Confirmed: " . ($isConfirmed ? 'YES' : 'NO');
                            @endphp

                            @if(in_array(auth()->user()->role_name, ['mandor', 'vendor']))
                                {{-- Tampilkan debug info --}}
                                <div class="debug-info" style="display: none;">{{ $debugInfo }}</div>
                                
                                @if($isConfirmed)
                                    <span class="btn btn-sm btn-success" 
                                        title="Dikonfirmasi pada {{ $confirmedAt }}"
                                        disabled>
                                        <i class="fas fa-check"></i>
                                    </span>
                                @else
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-secondary confirm-btn" 
                                        data-spt-id="{{ $spt->id }}" 
                                        title="Konfirmasi SPT">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            @elseif($isConfirmed)
                                <span class="btn btn-sm btn-success" 
                                    title="Dikonfirmasi pada {{ $confirmedAt }}">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                            
                            @if(!in_array(auth()->user()->role_name, ['Assistant Manager Plantation', 'Manager Plantation', 'mandor', 'Assistant Manager CDR', 'Manager CDR','vendor']))
                                @if($displayStatus !== 'Draft')
                                    <button type="button" class="btn btn-secondary btn-sm"
                                       onclick="event.preventDefault();
                                       Swal.fire({
                                           icon: 'warning',
                                           title: 'Tidak Dapat Mengedit',
                                           text: 'SPT dengan status {{ $displayStatus }} tidak dapat diedit karena sudah diajukan.',
                                           confirmButtonText: 'Mengerti'
                                       });">EDIT</button>
                                @else
                                    <a href="{{ route('spt.edit', $spt->id) }}" class="btn btn-secondary btn-sm">EDIT</a>
                                @endif
                                <form id="delete-form-{{ $spt->id }}" action="{{ route('spt.destroy', $spt->id) }}" method="POST" class="d-inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $spt->id }}">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-gray-500">Tidak ada data SPT yang ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($spts->hasPages())
    <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-500">
            Menampilkan
            @if($spts->count() > 0)
                {{ $spts->firstItem() }} - {{ $spts->lastItem() }}
            @else
                0
            @endif
            dari {{ $spts->total() }} data
        </div>
        <div class="ml-auto">
            {{ $spts->appends(request()->query())->links('pagination::vendor-angkut') }}
        </div>
    </div>
    @endif
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show success message if exists
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successMessage.dataset.message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Show error message if exists
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errorMessage.dataset.message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Close alert message
        function setupAlertCloseButtons() {
            document.querySelectorAll('.close-alert').forEach(button => {
                button.removeEventListener('click', handleAlertClose);
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

        // Initialize alert close buttons
        setupAlertCloseButtons();

        // Filter elements
        const filterToggle = document.getElementById('filter-toggle');
        const filterPanel = document.getElementById('filter-panel');
        const applyFilterBtn = document.getElementById('apply-filter');
        const resetFilterBtn = document.getElementById('reset-filter');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const tanggalMulaiInput = document.getElementById('tanggal_mulai_input');
        const tanggalSelesaiInput = document.getElementById('tanggal_selesai_input');
        const jenisTebangSelect = document.getElementById('jenis_tebang_select');
        const statusSelect = document.getElementById('status_select');

        let typingTimer;
        const typingInterval = 500; // 0.5 seconds

        // Toggle filter panel
        if (filterToggle && filterPanel) {
            filterToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                filterPanel.classList.toggle('show');
                console.log('Filter toggle clicked');
            });
        }

        // Apply filter
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Update hidden fields with filter values
                document.getElementById('tanggal_mulai').value = tanggalMulaiInput ? tanggalMulaiInput.value : '';
                document.getElementById('tanggal_selesai').value = tanggalSelesaiInput ? tanggalSelesaiInput.value : '';
                document.getElementById('filter_jenis_tebang').value = jenisTebangSelect ? jenisTebangSelect.value : '';
                document.getElementById('filter_status').value = statusSelect ? statusSelect.value : '';

                // Submit the form
                searchForm.submit();
            });
        }

        // Reset filter
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Reset all filter inputs
                if (tanggalMulaiInput) tanggalMulaiInput.value = '';
                if (tanggalSelesaiInput) tanggalSelesaiInput.value = '';
                if (jenisTebangSelect) jenisTebangSelect.value = '';
                if (statusSelect) statusSelect.value = '';
                if (searchInput) searchInput.value = '';

                // Clear hidden fields
                document.getElementById('tanggal_mulai').value = '';
                document.getElementById('tanggal_selesai').value = '';
                document.getElementById('filter_jenis_tebang').value = '';
                document.getElementById('filter_status').value = '';

                // Submit the form
                searchForm.submit();
            });
        }

        // Close filter panel when clicking outside
        document.addEventListener('click', function(event) {
            if (filterPanel && filterToggle &&
                !filterPanel.contains(event.target) &&
                !filterToggle.contains(event.target)) {
                filterPanel.classList.remove('show');
            }
        });

        // Search with debounce
        if (searchInput) {
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
        }

        // Show filter panel if any filter is active
        if (filterPanel && (
            (document.getElementById('tanggal_mulai') && document.getElementById('tanggal_mulai').value) ||
            (document.getElementById('tanggal_selesai') && document.getElementById('tanggal_selesai').value) ||
            (document.getElementById('filter_jenis_tebang') && document.getElementById('filter_jenis_tebang').value) ||
            (document.getElementById('filter_status') && document.getElementById('filter_status').value)
        )) {
            filterPanel.classList.add('show');
        }

        document.addEventListener('click', function (event) {
    const filterPanel = document.getElementById('filter-panel');
    const filterToggle = document.getElementById('filter-toggle');

    if (
        filterPanel.classList.contains('show') &&
        !filterPanel.contains(event.target) &&
        !filterToggle.contains(event.target)
    ) {
        filterPanel.classList.remove('show');
    }
});

        // Handle confirm button clicks for mandor
        document.addEventListener('click', async function(e) {
            const confirmBtn = e.target.closest('.confirm-btn');
            
            if (confirmBtn && !confirmBtn.disabled) {
                const sptId = confirmBtn.dataset.sptId;
                const icon = confirmBtn.querySelector('i');
                
                try {
                    // Tampilkan loading
                    icon.className = 'fas fa-spinner fa-spin';
                    
                    // Kirim request ke server
                    const response = await fetch(`/spt/${sptId}/confirm`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal mengonfirmasi SPT');
                    }
                    
                    // Update UI
                    icon.className = 'fas fa-check';
                    confirmBtn.classList.remove('btn-outline-secondary');
                    confirmBtn.classList.add('btn-success');
                    confirmBtn.disabled = true;
                    confirmBtn.title = 'Dikonfirmasi';
                    
                    // Tampilkan notifikasi sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'SPT berhasil dikonfirmasi',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                } catch (error) {
                    console.error('Error confirming SPT:', error);
                    icon.className = 'fas fa-check';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.message || 'Gagal mengonfirmasi SPT. Silakan coba lagi.',
                        confirmButtonText: 'Mengerti'
                    });
                }
                
                return;
            }
            
            // Handle delete button clicks with SweetAlert2 confirmation
            const deleteBtn = e.target.closest('.delete-btn');
            if (!deleteBtn) return;

            e.preventDefault();
            const row = deleteBtn.closest('tr');
            const status = row.querySelector('.status-badge').textContent.trim();
            
            // Check if SPT is not in draft status
            if (status !== 'Draft') {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Dapat Dihapus',
                    text: 'SPT sudah diserahkan, tidak bisa dihapus. Silakan ajukan yang baru',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            const form = document.getElementById('delete-form-' + deleteBtn.getAttribute('data-id'));

            const result = await Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data SPT ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
