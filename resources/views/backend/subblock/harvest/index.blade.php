@extends('layouts.master')

@php
$header = 'List Harvest Sub Block';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => $header]
];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link rel="stylesheet" href="{{ asset('css/activity-tracking.css') }}">
<style>
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        white-space: nowrap;
        text-align: center;
        min-width: 80px;
        line-height: 1.5;
    }
    
    .status-not_started {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .status-planned {
        background-color: #f3f4f6;
        color: #6b7280;
    }
    
    .status-in_progress {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-completed {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
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
    .vendor-container {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .vendor-header {
        margin-bottom: 1.5rem !important;
    }

    .menu-tabs-wrapper {
        margin-top: -0.5rem;
    }
</style>
@endpush

@section('content')

    <div class="vendor-container">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h2 class="mb-0" style="margin-top: -1.5rem;">List Harvest Sub Block</h2>
        </div>

        <!-- Menu Tabs -->
        <div class="menu-tabs-wrapper">
            <div class="menu-tabs">
                @can('view-sub-block-information')
                <a href="{{ route('sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('sub-blocks.*') ? 'active' : '' }}">Sub Block</a>
                @endcan
                @can('view-foreman-sub-block')
                <a href="{{ route('foreman-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('foreman-sub-blocks.*') ? 'active' : '' }}">Foreman Sub Block</a>
                @endcan
                @can('view-harvest-sub-block')
                <a href="{{ route('harvest-sub-blocks.index') }}" class="tab-button1 {{ request()->routeIs('harvest-sub-blocks.*') ? 'active' : '' }}">Harvest Sub Block</a>
                @endcan
            </div>
        </div>

        <div class="vendor-header">
            <!-- Search Form -->
            <form action="{{ route('harvest-sub-blocks.index') }}" method="GET" class="search-form" id="search-form">
                <div class="d-flex gap-2">
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" name="search" id="search-input" placeholder="Cari kode petak..."
                               value="{{ request('search') }}" class="form-control">
                        <button type="submit" class="btn btn-outline-secondary" title="Cari">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    @php
                        $selectedPriority = request('priority');
                        $priorityOptions = [
                            '1' => '1 - Prioritas Tertinggi',
                            '2' => '2 - Prioritas Menengah',
                            '3' => '3 - Prioritas Standar'
                        ];
                    @endphp
                    <div class="input-group" style="width: 200px;">
                        <select name="priority" class="form-select" onchange="this.form.submit()" style="padding: 0.375rem 0.75rem;">
                            <option value="" {{ $selectedPriority === '' ? 'selected' : '' }}>Semua Prioritas</option>
                            @foreach($priorityOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedPriority == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $selectedStatus = request('status');
                        $statusOptions = [
                            '' => 'Semua Status',
                            'planned' => 'Planned',
                            'not_started' => 'Not Started',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed'
                        ];
                    @endphp
                    <div class="input-group" style="width: 200px;">
                        <select name="status" class="form-select" onchange="this.form.submit()" style="padding: 0.375rem 0.75rem;">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('harvest-sub-blocks.index') }}" class="btn btn-outline-secondary d-flex align-items-center" title="Reset Pencarian">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="btn-group">
            @can('create-harvest-sub-block')
                <!-- Export to Excel Button -->
                <a href="{{ route('harvest-sub-blocks.export', request()->query()) }}" class="btn btn-success me-2">
                    <i></i> Export Excel
                </a>
                <!-- Add New Button -->
                <a href="{{ route('harvest-sub-blocks.create') }}" class="btn btn-primary">
                    <i></i> Tambah Data
                </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive">
            <table class="vendor-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Petak</th>
                        <th>Peta</th>
                        <th>Estate</th>
                        <th>Divisi</th>
                        <th>Luas Area (Ha)</th>
                        <th>Musim Panen</th>
                        <th>Umur (Bln)</th>
                        <th>Estimasi (Ton/Ha)</th>
                        <th>Rencana Panen</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Keterangan</th>
                        @canany(['edit-harvest-sub-block', 'delete-harvest-sub-block'])
                        <th class="text-center">Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse ($harvestSubBlocks as $harvest)
                        <tr>
                            <td>{{ $harvestSubBlocks->firstItem() + $loop->index }}</td>
                            <td>{{ $harvest->kode_petak }}</td>
                            <td>
                                @if(isset($harvest->subBlock) && !empty($harvest->subBlock->geom_json))
                                    <a href="{{ route('gis.index') }}?focusHarvest={{ $harvest->kode_petak }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Lihat di Peta GIS"
                                       target="_blank">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $harvest->subBlock->estate ?? '-' }}</td>
                            <td>{{ $harvest->subBlock->divisi ?? '-' }}</td>
                            <td class="text-end">{{ number_format($harvest->subBlock->luas_area ?? 0, 2) }}</td>
                            <td>{{ $harvest->harvest_season }}</td>
                            <td class="text-center">{{ $harvest->age_months }}</td>
                            <td class="text-end">{{ number_format($harvest->yield_estimate_tph, 2) }}</td>
                            <td>{{ $harvest->planned_harvest_date ? \Carbon\Carbon::parse($harvest->planned_harvest_date)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center">
                                @php
                                // Ambil status dari tracking activity pertama jika ada
                                $status = 'planned'; // default status
                                if ($harvest->trackingActivity->isNotEmpty()) {
                                    $status = $harvest->trackingActivity->first()->status_tracking;
                                }

                                $validStatuses = ['not_started', 'in_progress', 'completed'];
                                if (!in_array($status, $validStatuses)) {
                                    $status = 'planned';
                                }

                                // Pastikan hanya pakai 4 status utama
                                $statusMap = [
                                    'planned' => 'Planned',
                                    'not_started' => 'Not Started',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                ];

                                // Pakai fallback ke 'Planned' kalau status tidak dikenal
                                $label = $statusMap[$status] ?? 'Planned';

                                // Class warna badge
                                $statusClass = [
                                    'planned' => 'status-planned',
                                    'not_started' => 'status-not_started',
                                    'in_progress' => 'status-in_progress',
                                    'completed' => 'status-completed',
                                ][$status] ?? 'status-planned';
                            @endphp

                            <span class="status-badge {{ $statusClass }}" style="text-transform: uppercase;">{{ $label }}</span>
                            </td>

                            <td class="text-center">{{ $harvest->priority_level }}</td>
                            <td>{{ $harvest->remarks ?? '-' }}</td>
                            @canany(['edit-harvest-sub-block', 'delete-harvest-sub-block'])
                            <td>
                                <div class="action-buttons">
                                    @can('edit-harvest-sub-block')
                                    <a href="{{ route('harvest-sub-blocks.edit', $harvest->id) }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i>Edit
                                    </a>
                                    @endcan
                                    @can('delete-harvest-sub-block')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-delete-url="{{ route('harvest-sub-blocks.destroy', $harvest->id) }}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                    @endcan
                                </div>
                            </td>
                            @endcanany
                        </tr>
                    @empty
                        @php
                            $aksiEnabled = auth()->user()->can('edit-harvest-sub-block') || auth()->user()->can('delete-harvest-sub-block');
                            $colspan = $aksiEnabled ? 13 : 12;
                        @endphp
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p class="mt-2">Tidak ada data yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($harvestSubBlocks->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Menampilkan {{ $harvestSubBlocks->firstItem() ?? 0 }} - {{ $harvestSubBlocks->lastItem() ?? 0 }} dari {{ $harvestSubBlocks->total() }} data
                    </div>
                    <nav aria-label="Page navigation">
                        {{ $harvestSubBlocks->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Peta Sub Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tF/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    #map {
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    .leaflet-container {
        height: 100%;
        width: 100%;
    }
</style>
@endpush

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
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

                    // Get CSRF token
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfMeta) {
                        throw new Error('CSRF token not found');
                    }
                    const csrfToken = csrfMeta.getAttribute('content');

                    // Send DELETE request using fetch API
                    const response = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new URLSearchParams({
                            _method: 'DELETE',
                            _token: csrfToken
                        })
                    });

                    let data;
                    const contentType = response.headers.get('content-type');

                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        // If it's HTML, assume success and use default success message
                        if (response.ok) {
                            data = { success: true, message: 'Data berhasil dihapus' };
                        } else {
                            throw new Error('Gagal menghapus data');
                        }
                    }

                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal menghapus data');
                    }

                    // Show success message
                    await Swal.fire({
                        title: 'Berhasil!',
                        text: data.message || 'Data berhasil dihapus',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    // Remove the row from the table
                    row.remove();

                    // Renumber the remaining rows
                    const rows = document.querySelectorAll('tbody tr');
                    const firstItem = parseInt('{{ $harvestSubBlocks->firstItem() }}');
                    rows.forEach((row, index) => {
                        const numberCell = row.querySelector('td:first-child');
                        if (numberCell) {
                            numberCell.textContent = firstItem + index;
                        }
                    });

                    // Check if table is empty
                    const tbody = document.querySelector('tbody');
                    if (tbody && tbody.children.length === 0) {
                        window.location.reload(); // Reload to show empty state
                    }

                } catch (error) {
                    console.error('Error:', error);
                    await Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menghapus data',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    // Reset button state
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-trash"></i>Hapus';
                    }
                }
            }
        });

        // Auto submit form on filter change
        document.querySelectorAll('select[onchange*="this.form.submit"]').forEach(select => {
            select.removeAttribute('onchange');
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });

    // Initialize map when modal is shown
    let map;
    let geoJsonLayer;

    $('#mapModal').on('shown.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const geometry = JSON.parse(button.data('geometry'));

        // Initialize map if not already done
        if (!map) {
            map = L.map('map').setView([-2.5, 118], 5);

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

        // Clear previous layers
        if (geoJsonLayer) {
            map.removeLayer(geoJsonLayer);
        }

        // Add the GeoJSON layer
        if (geometry) {
            geoJsonLayer = L.geoJSON(geometry, {
                style: {
                    color: '#3388ff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.2
                }
            }).addTo(map);

            // Fit bounds to the feature
            map.fitBounds(geoJsonLayer.getBounds());
        }
    });

    // Clean up when modal is closed
    $('#mapModal').on('hidden.bs.modal', function () {
        if (map) {
            map.off();
            map.remove();
            map = null;
            geoJsonLayer = null;
        }
    });
</script>
@endpush

@endsection
