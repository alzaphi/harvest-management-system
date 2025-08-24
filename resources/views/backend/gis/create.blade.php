@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<style>
    html, body {
        height: 100%;
        margin: 0;
    }

    .map-container {
        height: 100vh;
        width: 100%;
    }

    #map {
        width: 100%;
        height: 100%;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
        background:rgb(252, 250, 248);
    }

    .leaflet-popup-content {
        font-size: 14px;
    }

    .estate-label {
        background-color: rgba(255, 255, 255, 0.8);
        color: #000;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
        border: 1px solid #999;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
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
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle delete button clicks with SweetAlert2 confirmation
    document.addEventListener('DOMContentLoaded', function() {
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
                    const response = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-HTTP-Method-Override': 'DELETE',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    let data = {};

                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        // If not JSON, assume it's a redirect or HTML response
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }
                        const text = await response.text();
                        throw new Error('Unexpected response format');
                    }

                    if (response.ok) {
                        // Show success message
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Data berhasil dihapus.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page after successful deletion
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Gagal menghapus data');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menghapus data',
                        icon: 'error'
                    });
                }
            }
        });
    });
</script>
@endpush

<div class="vendor-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Maps Upload</h2>
    </div>

    <div class="menu-tabs-wrapper">
        <div class="menu-tabs">
            <a href="{{ route('gis.index') }}" class="tab-button1 {{ request()->routeIs('gis.index') ? 'active' : '' }}">Maps View</a>
            <a href="{{ route('gis.create') }}" class="tab-button1 {{ request()->routeIs('gis.create') ? 'active' : '' }}">Maps Upload</a>
        </div>
    </div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Riwayat Upload</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary" onclick="toggleUploadForm()">
                <i class="fas fa-upload me-1"></i> Upload GeoJSON
            </button>
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#importLayakTebangModal">
                <i class="fas fa-file-import me-1"></i> Import Layak Tebang
            </button>
        </div>
    </div>
    <!-- Form upload yang disembunyikan dulu -->
<div id="uploadFormContainer" style="display: none;" class="card p-4 mt-3">
    <form id="uploadForm" action="{{ route('gis.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="geojson_file" class="form-label">File GeoJSON</label>
            <input type="file" name="geojson_file" id="geojson_file" accept=".geojson,.json" class="form-control" required>
            <div id="uploadProgress" class="progress mt-2" style="display: none; height: 20px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
            </div>
            <small id="uploadStatus" class="form-text text-muted"></small>
        </div>
        <div class="mb-3">
            <label for="uploaded_by" class="form-label">Uploaded By</label>
            <input type="text" name="uploaded_by" id="uploaded_by" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="estate_name" class="form-label">Estate</label>
            <input type="text" name="estate_name" id="estate_name" class="form-control">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Keterangan</label>
            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="update_existing" name="update_existing" value="1">
            <label class="form-check-label" for="update_existing">Perbarui data yang sudah ada</label>
            <small class="form-text text-muted d-block">
                Jika dicentang: Update data yang sudah ada berdasarkan kode_petak. Jika tidak, abaikan data yang sudah ada.
            </small>
        </div>
        <button type="submit" id="uploadButton" class="btn btn-primary">Upload</button>
    </form>
</div>

<script>
    function toggleUploadForm() {
        const form = document.getElementById('uploadFormContainer');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>
    <table class="vendor-table">
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Estate</th>
                <th>Diunggah Oleh</th>
                <th>Tanggal Unggah</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($maps as $map)
            <tr>
                <td>{{ $map->file_name }}</td>
                <td>{{ $map->estate_name }}</td>
                <td>{{ $map->uploaded_by }}</td>
                <td>{{ $map->upload_date }}</td>
                <td>{{ $map->description }}</td>
                <td class="text-center">
                    <div class="action-buttons">
                        <a href="{{ route('gis.edit', $map->id) }}" class="btn btn-sm btn-success btn-action" title="Edit data">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-action delete-btn"
                                data-delete-url="{{ route('gis.destroy', $map->id) }}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
    <div class="mt-3">
        {{ $maps->links() }}
    </div>
</div>






</div>




<div class="card p-4 mt-3">

<!-- Import Layak Tebang Modal -->
<div class="modal fade" id="importLayakTebangModal" tabindex="-1" aria-labelledby="importLayakTebangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLayakTebangModalLabel">Import Layak Tebang GeoJSON</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('harvest.import') }}" method="POST" enctype="multipart/form-data" id="importLayakTebangForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="geojson_file" class="form-label">Pilih File GeoJSON Layak Tebang</label>
                        <input type="file" name="geojson_file" class="form-control" accept=".geojson,.json" required>
                        <div class="form-text">Pilih file GeoJSON yang berisi data layak tebang.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-file-import me-1"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB chunks
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('geojson_file');
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('uploadProgress');
    const statusText = document.getElementById('uploadStatus');
    const uploadButton = document.getElementById('uploadButton');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const file = fileInput.files[0];
        if (!file) {
            alert('Silakan pilih file terlebih dahulu');
            return;
        }

        // Show progress bar
        progressContainer.style.display = 'block';
        uploadButton.disabled = true;
        statusText.textContent = 'Mempersiapkan upload...';

        try {
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            let uploadedChunks = 0;
            let fileId = Date.now().toString(); // Unique ID for this upload
            
            // Upload each chunk
            for (let chunkNumber = 0; chunkNumber < totalChunks; chunkNumber++) {
                const start = chunkNumber * CHUNK_SIZE;
                const end = Math.min(file.size, start + CHUNK_SIZE);
                const chunk = file.slice(start, end);
                
                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('chunkNumber', chunkNumber);
                formData.append('totalChunks', totalChunks);
                formData.append('fileId', fileId);
                formData.append('fileName', file.name);
                formData.append('_token', '{{ csrf_token() }}');
                
                // Add form data
                formData.append('uploaded_by', document.getElementById('uploaded_by').value);
                formData.append('estate_name', document.getElementById('estate_name').value);
                formData.append('description', document.getElementById('description').value);
                formData.append('update_existing', document.getElementById('update_existing').checked ? '1' : '0');

                // Update progress
                const progress = Math.round((chunkNumber / totalChunks) * 100);
                progressBar.style.width = `${progress}%`;
                progressBar.textContent = `${progress}%`;
                statusText.textContent = `Mengunggah bagian ${chunkNumber + 1} dari ${totalChunks}...`;

                // Upload chunk
                const response = await fetch('{{ route("gis.upload.chunk") }}', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Gagal mengunggah bagian file');
                }

                uploadedChunks++;
            }

            // Finalize upload
            statusText.textContent = 'Menyelesaikan upload...';
            
            const response = await fetch('{{ route("gis.merge.chunks") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fileId: fileId,
                    fileName: file.name,
                    uploaded_by: document.getElementById('uploaded_by').value,
                    estate_name: document.getElementById('estate_name').value,
                    description: document.getElementById('description').value,
                    update_existing: document.getElementById('update_existing').checked ? '1' : '0'
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                statusText.textContent = 'Upload berhasil!';
                
                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'File berhasil diunggah dan diproses.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Reload the page after successful upload
                    window.location.reload();
                });
            } else {
                throw new Error(result.message || 'Gagal menyatukan file');
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            statusText.textContent = 'Error: ' + error.message;
            statusText.className = 'text-danger';
            
            Swal.fire({
                title: 'Error!',
                text: 'Gagal mengunggah file: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
        } finally {
            uploadButton.disabled = false;
        }
    });
});
</script>
@endpush

@endsection
