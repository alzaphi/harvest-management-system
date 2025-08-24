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
        <h2 class="mb-0">List Sub Block</h2>
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

    <div class="vendor-header mb-4">
        <!-- Search Form -->
        <form action="{{ route('sub-blocks.index') }}" method="GET" class="search-form" id="search-form">
            <div class="filter-group">
                <input type="text" name="kode_petak" id="search-input" placeholder="Cari kode petak..."
                    value="{{ request('kode_petak') }}" class="search-input">
            </div>
        </form>

        <!-- Button Group -->
        <div class="btn-group">
            <button type="button" class="btn btn-primary tambah-vendor-btn" data-bs-toggle="modal" data-bs-target="#downloadPetakJbmModal">
                <i class="fas fa-download me-1"></i> Download Petak JBM GeoJSON
            </button>
            @can('download-layak-tebang')
            <a href="{{ route('sub-blocks.download.layak-tebang') }}" class="btn btn-success tambah-vendor-btn">
                <i class="fas fa-download me-1"></i> Download Layak Tebang GeoJSON
            </a>
            @endcan
            @can('create-sub-block-information')
            <a href="{{ route('sub-blocks.create') }}" class="btn btn-primary tambah-vendor-btn">
                <i class="fas fa-plus me-1"></i> Tambah Data
            </a>
            @endcan
        </div>
    </div>
    <!-- Form Modal -->
    <div class="modal fade" id="subblockModal" tabindex="-1" aria-labelledby="subblockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subblockModalLabel">Tambah Sub Block</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('sub-blocks.store') }}" method="POST" id="subblockForm">
                        @csrf
                        <input type="hidden" id="id" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Sub Block</label>
                                <input type="text" name="kode_petak" class="form-control" id="kodePetak" readonly/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estate</label>
                                <select name="estate" class="form-select" id="estateSelect">
                                    <option value="">Pilih Estate</option>
                                    <option>Langkoala Estate</option>
                                    <option>Poleang Estate</option>
                                    <option>Riset</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Divisi</label>
                                <select name="divisi" class="form-select" id="divisionSelect">
                                    <option value="">Pilih Divisi</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Blok</label>
                                <select name="blok" class="form-select" id="blockSelect">
                                    <option value="">Pilih Blok</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Luas Area (ha)</label>
                                <input name="luas_area" type="number" step="0.01" class="form-control" id="luasArea"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zona</label>
                                <select name="zona" class="form-select" id="zonaInput">
                                    <option value="">Pilih Zona</option>
                                    <option>Zone 1</option>
                                    <option>Zone 2</option>
                                    <option>Zone 3</option>
                                    <option>Zone 4</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" id="keteranganInput"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="aktif" id="aktifCheckbox" value="1" checked>
                                    <label class="form-check-label" for="aktifCheckbox">
                                        Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="saveBtn" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Table Section -->
    <table class="vendor-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Petak</th>
                <th>Estate</th>
                <th>Divisi</th>
                <th>Blok</th>
                <th>Luas Area (Ha)</th>
                <th>Umur (Bln)</th>
                <th>Zona</th>
                <th>Peta</th>
                <th>Status</th>
                <th>Keterangan</th>
                @canany(['edit-sub-block-information', 'delete-sub-block-information'])
                <th>Aksi</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @forelse ($subblocks as $index => $subblock)
                <tr>
                    <td>{{ $subblocks->firstItem() + $loop->index }}</td>
                    <td>{{ $subblock->kode_petak }}</td>
                    <td>{{ $subblock->estate }}</td>
                    <td>{{ $subblock->divisi }}</td>
                    <td>{{ $subblock->blok }}</td>
                    <td>{{ number_format($subblock->luas_area, 2) }}</td>
                    <td>{{ $subblock->age_months }}</td>
                    <td>{{ $subblock->zona ?? '-' }}</td>
                    <td>
                        @if($subblock->geom_json)
                            <a href="{{ route('gis.index') }}?focus={{ $subblock->kode_petak }}" title="Lihat di Peta">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ $subblock->aktif ? 'active' : 'inactive' }}">
                            {{ $subblock->aktif ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>

                    <td>{{ $subblock->keterangan ? Str::limit($subblock->keterangan, 30) : '-' }}</td>
                    @canany(['edit-sub-block-information', 'delete-sub-block-information'])
                    <td>
                        @can('edit-sub-block-information')
                        <a href="{{ route('sub-blocks.edit', $subblock->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i>Edit</a>
                        @endcan
                        @can('delete-sub-block-information')
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-delete-url="{{ route('sub-blocks.destroy', $subblock->id) }}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        @endcan
                    </td>
                    @endcanany
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-gray-500">Tidak ada data sub block yang ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    @if($subblocks->hasPages())
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan {{ $subblocks->firstItem() ?? 0 }} - {{ $subblocks->lastItem() ?? 0 }} dari {{ $subblocks->total() }} data
                </div>
                <nav aria-label="Page navigation">
                    {{ $subblocks->onEachSide(1)->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        </div>
    @endif

</div>

<!-- Upload GeoJSON Modal -->
<div class="modal fade" id="uploadGeoJsonModal" tabindex="-1" aria-labelledby="uploadGeoJsonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadGeoJsonForm" action="{{ route('sub-blocks.import-geojson') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadGeoJsonModalLabel">Upload GeoJSON File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="geojsonFile" class="form-label">Pilih file GeoJSON</label>
                        <input class="form-control" type="file" id="geojsonFile" name="geojson_file" accept=".geojson,.json" required>
                        <div class="form-text">Unggah file GeoJSON yang berisi data sub-blok</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="updateExisting" name="update_existing">
                        <label class="form-check-label" for="updateExisting">
                            Perbarui data yang sudah ada
                        </label>
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

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Handle GeoJSON form submission
$(document).ready(function() {
    $('#uploadGeoJsonForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const formData = new FormData(form[0]);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();

        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengunggah...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Close the modal
                $('#uploadGeoJsonModal').modal('hide');

                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Reload the page to show updated data
                        window.location.reload();
                    });
                } else {
                    // Show warning with errors
                    let errorMessage = response.message;
                    if (response.errors && response.errors.length > 0) {
                        errorMessage += '\n\n' + response.errors.join('\n');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        html: errorMessage.replace(/\n/g, '<br>'),
                        confirmButtonText: 'Mengerti'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat mengunggah file';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonText: 'Tutup'
                });
            },
            complete: function() {
                // Reset form and button state
                submitBtn.prop('disabled', false).html(originalBtnText);
                form.trigger('reset');
            }
        });
    });

    // Reset form when modal is closed
    $('#uploadGeoJsonModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle Select All Estates checkbox
        const selectAllCheckbox = document.getElementById('selectAllEstates');
        const estateCheckboxes = document.querySelectorAll('.estate-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                estateCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Update Select All when individual checkboxes change
            estateCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(estateCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }

        // Handle form submission
        const downloadForm = document.getElementById('downloadPetakJbmForm');
        if (downloadForm) {
            downloadForm.addEventListener('submit', function(e) {
                const checkedBoxes = document.querySelectorAll('.estate-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Pilih setidaknya satu estate untuk didownload',
                        confirmButtonText: 'Mengerti'
                    });
                }
            });
        }
        // Debug: Simple message to check if JS is working
        console.log('JavaScript is loaded and running');
        // Handle tab menu clicks
        document.querySelectorAll('.menu-tabs a').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all tabs
                document.querySelectorAll('.menu-tabs a').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                // Navigate to the link
                window.location.href = this.getAttribute('href');
            });
        });

        // Search functionality with debounce is now handled in the main script

        // CSRF Token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Form mode state
        let formMode = 'create'; // 'create' or 'edit'
        let currentSubBlockId = null;

        // Initialize form elements
        const subblockForm = document.getElementById('subblockForm');
        const estateSelect = document.getElementById('estateSelect');
        const divisionSelect = document.getElementById('divisionSelect');
        const blockSelect = document.getElementById('blockSelect');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const newBtn = document.getElementById('newBtn');
        const saveBtn = document.getElementById('saveBtn');

        // Estate and division options
        const options = @json($estatesWithDivisions);

        // Initialize Bootstrap modal
        const subblockModal = new bootstrap.Modal(document.getElementById('subblockModal'));

        // Initialize form state
        const resetForm = () => {
            if (subblockForm) {
                subblockForm.reset();
                subblockForm.action = '{{ route("sub-blocks.store") }}';

                // Remove any existing _method input
                const methodInput = subblockForm.querySelector('input[name="_method"]');
                if (methodInput) {
                    subblockForm.removeChild(methodInput);
                }
            }

            currentSubBlockId = null;
            formMode = 'create';

            // Reset selects
            if (estateSelect) estateSelect.value = '';
            if (divisionSelect) divisionSelect.innerHTML = '<option value="">Pilih Divisi</option>';
            if (blockSelect) blockSelect.innerHTML = '<option value="">Pilih Blok</option>';

            // Update modal title
            const modalTitle = document.getElementById('subblockModalLabel');
            if (modalTitle) modalTitle.textContent = 'Tambah Sub Block';

            // Generate kode petak for new entries
            generateKodePetak();
        };

        // Handle estate selection change
        if (estateSelect) {
            estateSelect.addEventListener('change', function() {
                const estate = this.value;
                updateDivisionOptions(estate);
                generateKodePetak();
            });
        }

        // Update division options based on selected estate
        function updateDivisionOptions(estate) {
            if (!divisionSelect) return;

            divisionSelect.innerHTML = '<option value="">Pilih Divisi</option>';

            if (estate && options[estate]) {
                options[estate].forEach(division => {
                    const option = document.createElement('option');
                    option.value = division;
                    option.textContent = division;
                    divisionSelect.appendChild(option);
                });
            }
        }

        // Generate kode petak based on estate
        function generateKodePetak() {
            const kodePetakInput = document.getElementById('kodePetak');
            if (!kodePetakInput || formMode !== 'create' || !estateSelect || !estateSelect.value) return;

            const estateCode = estateSelect.value.substring(0, 3).toUpperCase();
            const randomNum = Math.floor(100 + Math.random() * 900);
            kodePetakInput.value = `${estateCode}${randomNum}`;
        }

        // Handle new button click
        if (newBtn) {
            newBtn.addEventListener('click', function() {
                resetForm();
                if (subblockModal) subblockModal.show();
            });
        }

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                formMode = 'edit';
                currentSubBlockId = this.getAttribute('data-id');

                // Set form action for update
                if (subblockForm) {
                    subblockForm.action = `/sub-blocks/${currentSubBlockId}`;

                    // Add method spoof for PUT
                    let methodInput = subblockForm.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        subblockForm.appendChild(methodInput);
                    }
                    methodInput.value = 'PUT';
                }

                // Fill form data
                document.getElementById('id').value = currentSubBlockId;
                document.getElementById('kodePetak').value = this.getAttribute('data-kode');

                // Set estate and update divisions
                const estate = this.getAttribute('data-estate');
                if (estateSelect) estateSelect.value = estate;
                updateDivisionOptions(estate);

                // Set other fields
                document.getElementById('divisionSelect').value = this.getAttribute('data-divisi');
                document.getElementById('blockSelect').value = this.getAttribute('data-blok');
                document.getElementById('luasArea').value = this.getAttribute('data-luas');
                document.getElementById('zonaInput').value = this.getAttribute('data-zona');
                document.getElementById('keteranganInput').value = this.getAttribute('data-keterangan');
                document.getElementById('aktifCheckbox').checked = this.getAttribute('data-aktif') === '1';

                // Update modal title
                const modalTitle = document.getElementById('subblockModalLabel');
                if (modalTitle) modalTitle.textContent = 'Edit Sub Block';

                if (subblockModal) subblockModal.show();
            });
        });

        // Handle form submission
        if (subblockForm) {
            subblockForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const url = this.action;
                const method = formData.get('_method') || 'POST';

                // Show loading state
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                }

                fetch(url, {
                    method: method === 'PUT' ? 'POST' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert('success', data.message || 'Data berhasil disimpan');
                        // Reload the page to show updated data
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', error.message || 'Terjadi kesalahan saat menyimpan data');
                })
                .finally(() => {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Simpan';
                    }
                });
            });
        }

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
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            _method: 'DELETE',
                            _token: csrfToken
                        })
                    });

                    const data = await response.json();

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

                    // Reload the page to update the table
                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    // Reset button state
                    button.disabled = false;
                    button.innerHTML = originalText;

                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menghapus data',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

        // Search functionality with debounce
        let typingTimer;
        const doneTypingInterval = 500;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    if (searchForm) searchForm.submit();
                }, doneTypingInterval);
            });
        }

        // Helper function to show alerts
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);

                // Auto-remove alert after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        }

        // Initialize form
        resetForm();
    });

    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a');
        if (!paginationLink) return;

        e.preventDefault();

        const url = new URL(paginationLink.href);
        const page = url.searchParams.get('page') || 1;

        // Show loading state
        const paginationContainer = document.querySelector('.pagination-container');
        if (paginationContainer) {
            paginationContainer.innerHTML = '<p class="text-center py-4">Loading...</p>';
        }

        // Fetch the next page
        fetch(`${window.location.pathname}?page=${page}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Create a temporary container
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Update the table content
            const newTable = temp.querySelector('table.vendor-table');
            const currentTable = document.querySelector('table.vendor-table');
            if (newTable && currentTable) {
                currentTable.parentNode.replaceChild(newTable, currentTable);
            }

            // Update pagination
            const newPagination = temp.querySelector('.pagination');
            const currentPagination = document.querySelector('.pagination');
            if (newPagination && currentPagination) {
                currentPagination.parentNode.replaceChild(newPagination, currentPagination);
            } else if (newPagination) {
                const container = document.createElement('div');
                container.className = 'flex justify-center mt-6';
                container.appendChild(newPagination);
                document.querySelector('.vendor-container').appendChild(container);
            }

            // Update URL without page reload
            window.history.pushState({}, '', url);

            // Re-initialize any event listeners
            document.dispatchEvent(new Event('DOMContentLoaded'));
        })
        .catch(error => {
            console.error('Error loading page:', error);
            window.location.href = url.href; // Fallback to full page load
        });
    });
</script>

<style>
    /* Pagination styling */
    .pagination {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.75rem;
        --bs-pagination-border-radius: 0.2rem;
        margin-bottom: 0.5rem;
        margin-bottom: 0;
    }

    .page-link {
        color: #0d6efd;
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .page-item:first-child .page-link {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }

    .page-item:last-child .page-link {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
</style>
@endpush

<!-- Download Petak JBM GeoJSON Modal -->
<div class="modal fade" id="downloadPetakJbmModal" tabindex="-1" aria-labelledby="downloadPetakJbmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadPetakJbmModalLabel">Pilih Estate untuk Download</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="downloadPetakJbmForm" action="{{ route('sub-blocks.export-geojson') }}" method="GET">
                <div class="modal-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="selectAllEstates" checked>
                        <label class="form-check-label fw-bold" for="selectAllEstates">
                            Pilih Semua Estate
                        </label>
                    </div>
                    <div class="ms-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input estate-checkbox" type="checkbox" name="estates[]" value="LKL" id="estateLKL" checked>
                            <label class="form-check-label" for="estateLKL">
                                Langkoala Estate (LKL)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input estate-checkbox" type="checkbox" name="estates[]" value="PLG" id="estatePLG" checked>
                            <label class="form-check-label" for="estatePLG">
                                Poleang Estate (PLG)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input estate-checkbox" type="checkbox" name="estates[]" value="RST" id="estateRST" checked>
                            <label class="form-check-label" for="estateRST">
                                Riset (RST)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
