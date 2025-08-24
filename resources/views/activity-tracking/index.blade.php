@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link rel="stylesheet" href="{{ asset('css/spt.css') }}">
<link rel="stylesheet" href="{{ asset('css/activity-tracking.css') }}">
@endpush

@php
    $header = 'Harvest Activity Tracking';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Harvest Activity Tracking', 'url' => route('activity.tracking.index')]
    ];
@endphp

@section('content')
<!-- Alert -->
@if(session('success'))
    <div id="success-message" data-message="{{ session('success') }}"></div>
@endif

@if(session('error'))
    <div id="error-message" data-message="{{ session('error') }}"></div>
@endif

<div class="vendor-container">
    <h2>Harvest Activity Tracking</h2>

    <div class="vendor-header mb-4">
        <div class="flex items-center space-x-4">
            <form action="{{ route('activity.tracking.index') }}" method="GET" class="search-form flex-grow" id="search-form">
                <div class="filter-group relative">
                    <input type="text" name="search" id="search-input"
                           class="search-input w-full"
                           placeholder="Cari berdasarkan kode SPT, kode petak, atau vendor..."
                           value="{{ request('search') }}">
                    <button type="submit" class="search-button">
                    </button>
                </div>
            </form>

            <div class="filter-container">
                <button type="button" id="filter-toggle" class="btn btn-secondary flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Filter
                </button>
                <div class="filter-panel" id="filter-panel">
                    <h3 class="text-lg font-medium mb-4">Filter Data</h3>
                    <form id="filter-form" method="GET" action="{{ route('activity.tracking.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tanggal Mulai -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" 
                                       value="{{ request('start_date') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Tanggal Selesai -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" 
                                       value="{{ request('end_date') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Semua Status</option>
                                    <option value="not_started" {{ request('status') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="reset-filter" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="activity-container">
        <table class="vendor-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode SPT</th>
                    <th>Kode Petak</th>
                    <th>Nama Vendor</th>
                    <th>Nama Mandor</th>
                    <th>Status</th>
                    <th>Jumlah LKT</th>
                    <th>Diperbarui</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trackingList as $index => $activity)
                <tr data-activity-id="{{ $activity->id }}">
                    <td class="text-center">{{ $trackingList->firstItem() + $loop->index }}</td>
                    <td>{{ $activity->kode_spt }}</td>
                    <td>{{ $activity->kode_petak }}</td>
                    <td>
                        @if($activity->spt->vendor)
                            <div class="font-medium">{{ $activity->spt->vendor->nama_vendor }}</div>
                            <div class="text-xs text-gray-500">{{ $activity->spt->vendor->kode_vendor }}</div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </td>
                    <td>
                        @if($activity->spt->mandor)
                            <div class="font-medium">{{ $activity->spt->mandor->nama_mandor }}</div>
                            <div class="text-xs text-gray-500">{{ $activity->spt->mandor->kode_mandor }}</div>
                        @else
                            <div class="text-gray-500">-</div>
                        @endif
                    </td>
                    <td>
                        @php
                            // Get LKT count from the relationship
                            $lktCount = $activity->spt->lkt_count ?? 0;
                            
                            // If LKT count is 0, force status to 'Not Started'
                            $status = ($lktCount === 0) ? 'Not Started' : $activity->status_tracking;
                            
                            $statusMap = [
                                'not_started' => 'Not Started',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed'
                            ];
                            $statusText = $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                            // Convert status to lowercase and replace spaces/dashes with underscores for the CSS class
                            $statusClass = 'status-' . strtolower(str_replace([' ', '-'], '_', $status));
                        @endphp
                        <span class="status-badge {{ $statusClass }}" style="text-transform: uppercase;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="text-center">{{ $activity->spt->lkt_count ?? 0 }}</td>
                    <td>{{ $activity->updated_at->format('d/m/Y H:i') }}</td>
                    <td class="whitespace-nowrap">
                        <div class="button-container">
                            <button class="btn-action btn-detail" onclick="showSPTDetail('{{ $activity->id }}')">
                                <i class="fas fa-eye mr-1"></i> Lihat Detail
                            </button>
                            @can('edit-track-activity')
                            <div class="dropdown-container">
                                <button class="btn-action btn-status dropdown-toggle" type="button" id="dropdownMenuButton{{ $activity->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-sync-alt mr-1"></i> Ubah Status
                                </button>
                                <div class="dropdown-menu hidden" aria-labelledby="dropdownMenuButton{{ $activity->id }}">
                                    <button class="dropdown-item" onclick="updateStatus('{{ $activity->id }}', 'not_started')">
                                        <i class="fas fa-hourglass-start mr-2"></i> Not Started
                                    </button>
                                    <button class="dropdown-item" onclick="updateStatus('{{ $activity->id }}', 'in_progress')">
                                        <i class="fas fa-spinner mr-2"></i> In Progress
                                    </button>
                                    <button class="dropdown-item" onclick="updateStatus('{{ $activity->id }}', 'completed')">
                                        <i class="fas fa-check-circle mr-2"></i> Completed
                                    </button>
                                </div>
                            </div>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">Tidak ada data yang ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($trackingList->hasPages())
        <div class="flex justify-between items-center mt-4">
            <div class="text-sm text-gray-500">
                Menampilkan
                @if($trackingList->count() > 0)
                    {{ $trackingList->firstItem() }} - {{ $trackingList->lastItem() }}
                @else
                    0
                @endif
                dari {{ $trackingList->total() }} data
            </div>
            <div class="ml-auto">
                {{ $trackingList->appends(request()->query())->links('pagination::vendor-angkut') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- SPT Detail Modal -->
<div id="sptDetailModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-semibold">Detail Aktivitas</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <span class="text-2xl">&times;</span>
            </button>
        </div>
        
        <div id="sptDetailContent">
            <!-- Detail content will be populated via AJAX -->
        </div>
        
        <div class="mt-5">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showSPTDetail(sptId) {
        // In a real application, you would fetch this data via AJAX
        // This is a mock implementation
        const mockData = {
            subBlocks: [
                { code: 'AB01B', status: 'In Progress' },
                { code: 'AB02A', status: 'Not Started' },
                { code: 'AB03C', status: 'Completed' }
            ],
            lkts: [
                { no: 'LKT-001', date: '2023-06-15', status: 'Completed' },
                { no: 'LKT-002', date: '2023-06-16', status: 'In Progress' },
                { no: 'LKT-003', date: '2023-06-17', status: 'Not Started' }
            ]
        };

        // Populate sub-block status
        const subBlockStatus = document.getElementById('subBlockStatus');
        subBlockStatus.innerHTML = mockData.subBlocks.map(block => 
            `<div class="flex items-center">
                <span class="font-medium">Petak ${block.code}</span>
                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    ${getStatusClass(block.status)}">
                    ${block.status}
                </span>
            </div>`
        ).join('');

        // Populate LKT list
        const lktList = document.getElementById('lktList');
        lktList.innerHTML = mockData.lkts.map(lkt => `
            <tr>
                <td class="px-6 py-2 border-b text-center">${lkt.no}</td>
                <td class="px-6 py-2 border-b text-center">${lkt.date}</td>
                <td class="px-6 py-2 border-b text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(lkt.status)}">
                        ${lkt.status}
                    </span>
                </td>
            </tr>
        `).join('');

        // Show modal
        document.getElementById('sptDetailModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('sptDetailModal').classList.add('hidden');
    }

    function getStatusClass(status) {
        const classes = {
            'not_started': 'bg-yellow-100 text-yellow-800',
            'in_progress': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('sptDetailModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Search functionality
    document.getElementById('searchBtn').addEventListener('click', function() {
        const status = document.getElementById('status').value;
        const kodePetak = document.getElementById('kode_petak').value;
        
        // In a real application, you would submit the form or make an AJAX call here
        console.log('Searching with:', { status, kodePetak });
        // For now, we'll just show an alert
        alert('Search functionality will be implemented here.\nStatus: ' + status + '\nKode Petak: ' + kodePetak);
    });
</script>
@endpush
@endsection

@push('scripts')
<script>
    // Date range initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker if using a library like flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr("input[type=date]", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
        }

        // Reset filter button
        document.getElementById('reset-filter').addEventListener('click', function() {
            // Reset all form inputs
            const form = document.getElementById('filter-form');
            form.reset();
            
            // Remove date parameters from URL
            const url = new URL(window.location.href);
            url.searchParams.delete('start_date');
            url.searchParams.delete('end_date');
            window.location.href = url.toString();
        });
    });

    // Simple dropdown toggle
    document.addEventListener('click', function(e) {
        // If clicking a dropdown toggle button
        const toggleBtn = e.target.closest('.dropdown-toggle');
        
        // Close all dropdowns first
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
        
        // If clicking a toggle button, show its dropdown
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = toggleBtn.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.add('show');
            }
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle filter panel
        const filterToggle = document.getElementById('filter-toggle');
        const filterPanel = document.getElementById('filter-panel');
        
        if (filterToggle && filterPanel) {
            // Prevent event propagation for filter panel
            filterPanel.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Toggle filter panel
            filterToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                filterPanel.classList.toggle('show');
            });
            
            // Close filter panel when clicking outside
            document.addEventListener('click', function() {
                filterPanel.classList.remove('show');
            });
            
            // Prevent form submission from closing the panel
            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.stopPropagation();
                });
            }
        }
        
        // Reset filter
        const resetFilter = document.getElementById('reset-filter');
        if (resetFilter) {
            resetFilter.addEventListener('click', function() {
                window.location.href = '{{ route("activity.tracking.index") }}';
            });
        }
        
        // Show success/error messages
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            const message = successMessage.getAttribute('data-message');
            if (message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
        
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            const message = errorMessage.getAttribute('data-message');
            if (message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
    });
    
    // Function to show confirmation before updating status
    function updateStatus(activityId, newStatus) {
        // Map the status to display text
        const statusMap = {
            'not_started': 'Not Started',
            'in_progress': 'In Progress',
            'completed': 'Completed'
        };
        const statusText = statusMap[newStatus] || newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Konfirmasi Perubahan Status',
            text: `Anda yakin ingin mengubah status?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ubah Status',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, proceed with status update
                performStatusUpdate(activityId, newStatus);
            }
        });
    }
    
    // Function to update status in the UI
    function updateStatusInUI(activityId, newStatus, updatedAt) {
        const row = document.querySelector(`tr[data-activity-id="${activityId}"]`);
        if (!row) return;

        // Update the status badge in the table
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            // Map the status to display text
            const statusMap = {
                'not_started': 'Not Started',
                'in_progress': 'In Progress',
                'completed': 'Completed'
            };
            const statusText = statusMap[newStatus] || newStatus;
            
            // Update status text and class
            statusBadge.textContent = statusText;
            
            // Remove all status classes
            statusBadge.className = 'status-badge';
            
            // Add the appropriate status class
            const statusClass = 'status-' + newStatus.toLowerCase().replace(/\s+/g, '-');
            statusBadge.classList.add(statusClass);
        }
        
        // Update the timestamp
        const timestampCell = row.querySelector('td:nth-last-child(2)');
        if (timestampCell) {
            timestampCell.textContent = updatedAt || 'Baru saja';
        }
        
        // Update status in modal if it's open for this activity
        const modal = document.getElementById('sptDetailModal');
        if (!modal.classList.contains('hidden')) {
            const modalActivityId = modal.getAttribute('data-activity-id');
            if (modalActivityId && modalActivityId === activityId.toString()) {
                const modalStatusBadge = modal.querySelector('.status-badge');
                if (modalStatusBadge) {
                    const statusMap = {
                        'not_started': 'Not Started',
                        'in_progress': 'In Progress',
                        'completed': 'Completed'
                    };
                    const statusText = statusMap[newStatus] || newStatus;
                    
                    // Update status text and class
                    modalStatusBadge.textContent = statusText;
                    
                    // Remove all status classes
                    modalStatusBadge.className = 'status-badge';
                    
                    // Add the appropriate status class
                    const statusClass = 'status-' + newStatus.toLowerCase().replace(/\s+/g, '-');
                    modalStatusBadge.classList.add(statusClass);
                }
            }
        }
    }

    // Function to perform the actual status update
    function performStatusUpdate(activityId, newStatus) {
        // Show loading indicator
        Swal.fire({
            title: 'Memperbarui status...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get CSRF token
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const row = document.querySelector(`tr[data-activity-id="${activityId}"]`);

        // Send request to update status
        fetch(`/activity-tracking/${activityId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            
            if (!response.ok) {
                // Check if this is a validation error about LKT count
                if (data.error && data.error.includes('LKT masih 0')) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Dapat Mengubah Status',
                        text: data.error,
                        confirmButtonText: 'Mengerti'
                    });
                    return null;
                }
                // Check if this is a validation error about incomplete LKTs
                if (data.error && data.error.includes('LKT yang belum selesai')) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Dapat Menyelesaikan',
                        text: data.error,
                        confirmButtonText: 'Mengerti'
                    });
                    return null;
                }
                throw new Error(data.error || 'Gagal memperbarui status');
            }
            return data;
        })
        .then(data => {
            // Skip if we returned null due to LKT validation
            if (data === null) return;
            if (data.success) {
                // Close all dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });

                // Update the UI with the new status
                updateStatusInUI(activityId, data.status, data.updated_at);

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Status berhasil diperbarui',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Gagal memperbarui status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Terjadi kesalahan saat memperbarui status',
                confirmButtonText: 'Tutup'
            });
        });
    }

    // Function to show SPT detail in modal
    function showSPTDetail(activityId) {
        // Store the activity ID in the modal for later reference
        const modal = document.getElementById('sptDetailModal');
        modal.setAttribute('data-activity-id', activityId);
        
        // Show loading
        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch SPT detail via AJAX
        fetch(`/activity-tracking/${activityId}/detail`)
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                // Format petak information
                let petakStatus = (data.petak.status || 'not_started').toLowerCase();
                // Convert status to display text
                const statusMap = {
                    'not_started': 'Not Started',
                    'in_progress': 'In Progress',
                    'completed': 'Completed'
                };
                const petakStatusText = statusMap[petakStatus] || 
                                     petakStatus.charAt(0).toUpperCase() + petakStatus.slice(1).replace('_', ' ');
                // Ensure status is in the correct format for CSS classes
                const petakStatusClass = 'status-' + petakStatus.toLowerCase().replace(/[\s-]+/g, '_');
                
                // Use actual LKT data from the server
                const lkts = data.lkts && data.lkts.length > 0 ? data.lkts : [];

                // Format LKTs
                const lktsHtml = lkts.map(lkt => {
                    // Map LKT status to display text and class (case insensitive)
                    const lktStatusMap = {
                        'draft': { text: 'Draft', class: 'draft' },
                        'ajukan': { text: 'Diajukan', class: 'ajukan' },
                        'diajukan': { text: 'Diajukan', class: 'ajukan' },
                        'selesai': { text: 'Selesai', class: 'selesai' },
                        'completed': { text: 'Selesai', class: 'selesai' } // for backward compatibility
                    };
                    
                    // Normalize status to lowercase and trim whitespace
                    const normalizedStatus = lkt.status ? lkt.status.toString().toLowerCase().trim() : 'draft';
                    // Find matching status (case insensitive)
                    const statusEntry = Object.entries(lktStatusMap).find(([key]) => 
                        key.toLowerCase() === normalizedStatus.toLowerCase()
                    );
                    
                    const statusInfo = statusEntry ? lktStatusMap[statusEntry[0]] : { text: lkt.status || 'Draft', class: 'draft' };
                    const statusText = statusInfo.text;
                    const statusClass = 'status-' + statusInfo.class;
                    
                    return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-center">${lkt.kode_lkt || 'N/A'}</td>
                        <td class="px-4 py-2 text-center">${lkt.tanggal_tebang || 'N/A'}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="status-badge ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                    </tr>`;
                }).join('');
                
                // Show modal with SPT detail
                Swal.fire({
                    title: `Detail ${data.spt_code || ''}`,
                    width: '800px',
                    showCloseButton: true,
                    showConfirmButton: false,
                    html: `
                        <div class="text-left">
                            <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 rounded-md">
                                <div>
                                    <span class="text-sm text-gray-600">Kode Petak:</span>
                                    <span class="ml-2 font-medium">${data.petak.code || 'N/A'}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-600 mr-2">Status:</span>
                                    <span class="status-badge ${petakStatusClass} mr-3" id="modalStatusBadge">
                                        ${petakStatusText}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-medium text-gray-900">Daftar LKT</h4>
                                    <span class="text-sm text-gray-600">Total: ${lkts.length} LKT</span>
                                </div>
                                <div class="lkt-scroll-container">
                                    <table class="w-full table-fixed">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-3 text-center w-1/3">No. LKT</th>
                                                <th class="px-4 py-3 text-center w-1/3">Tanggal</th>
                                                <th class="px-4 py-3 text-center w-1/3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            ${lktsHtml}
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '800px'
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat detail SPT. Silakan coba lagi.',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
    }
</script>
@endpush
