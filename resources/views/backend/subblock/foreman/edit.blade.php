@extends('app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<style>
    .vendor-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .form-control, .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }
    .btn-primary {
        background-color: #3b82f6;
        color: white;
        border: 1px solid #3b82f6;
    }
    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .btn-secondary {
        background-color: #6b7280;
        color: white;
        border: 1px solid #6b7280;
    }
    .btn-secondary:hover {
        background-color: #4b5563;
        border-color: #4b5563;
    }
    .is-invalid {
        border-color: #ef4444;
    }
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #ef4444;
    }
    .was-validated .form-control:invalid ~ .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Edit Mandor Sub Block</h2>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="editForm" action="{{ route('foreman-sub-blocks.update', $foremanSubBlock->id) }}" method="POST" class="needs-validation" novalidate onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan?');">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0">Data Mandor Sub Block</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_petak" class="form-label">Kode Petak</label>
                            <select class="form-select @error('kode_petak') is-invalid @enderror" id="kode_petak" name="kode_petak" required>
                                <option value="">Pilih Kode Petak</option>
                                @foreach($subBlocks as $subBlock)
                                    <option value="{{ $subBlock->kode_petak }}" {{ old('kode_petak', $foremanSubBlock->kode_petak) == $subBlock->kode_petak ? 'selected' : '' }}>
                                        {{ $subBlock->kode_petak }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_petak')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="divisi" class="form-label">Divisi</label>
                            <input type="text" class="form-control @error('divisi') is-invalid @enderror" id="divisi" name="divisi" value="{{ old('divisi', $foremanSubBlock->divisi) }}" required>
                            @error('divisi')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_mandor" class="form-label">Kode Mandor</label>
                            <input type="text" class="form-control bg-light" id="kode_mandor" value="{{ old('kode_mandor', $foremanSubBlock->kode_mandor) }}" readonly style="background-color: #f8f9fa !important; cursor: not-allowed;">
                            <input type="hidden" name="kode_mandor" value="{{ old('kode_mandor', $foremanSubBlock->kode_mandor) }}">
                            @error('kode_mandor')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mandor" class="form-label">Nama Mandor</label>
                            <select class="form-select @error('nama_mandor') is-invalid @enderror" id="nama_mandor" name="nama_mandor" required>
                                <option value="">Pilih Nama Mandor</option>
                                @foreach($foremanNames as $name)
                                    <option value="{{ $name }}" {{ old('nama_mandor', $foremanSubBlock->nama_mandor) == $name ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nama_mandor')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_kerja" class="form-label">Tanggal Kerja</label>
                            <input type="date" class="form-control @error('tanggal_kerja') is-invalid @enderror" id="tanggal_kerja" name="tanggal_kerja" value="{{ old('tanggal_kerja', $foremanSubBlock->tanggal_kerja->format('Y-m-d')) }}" required>
                            @error('tanggal_kerja')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <div>
                <a href="{{ route('foreman-sub-blocks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Perubahan
            </button>
        </div>


    </form>
</div>

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Enable form validation and other functionality
    (function() {
        'use strict';

        // Store subBlocks data in a JavaScript variable
        var subBlocks = {};
        @foreach($subBlocks as $subBlock)
            subBlocks['{{ $subBlock->kode_petak }}'] = '{{ $subBlock->divisi }}';
            console.log('Mapped kode_petak: {{ $subBlock->kode_petak }} => {{ $subBlock->divisi }}');
        @endforeach

        console.log('subBlocks data:', subBlocks);

        // Function to update divisi based on selected kode_petak
        function updateDivisi(kodePetak) {
            console.log('updateDivisi called with kodePetak:', kodePetak);
            var divisiInput = document.getElementById('divisi');
            console.log('divisiInput:', divisiInput);

            if (kodePetak && subBlocks[kodePetak]) {
                console.log('Found divisi for', kodePetak, ':', subBlocks[kodePetak]);
                divisiInput.value = subBlocks[kodePetak];
            } else {
                console.log('No divisi found for kode_petak:', kodePetak);
                divisiInput.value = '';
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Add event listener for kode_petak change
            var kodePetakSelect = document.getElementById('kode_petak');
            if (kodePetakSelect) {
                kodePetakSelect.addEventListener('change', function() {
                    updateDivisi(this.value);
                });

                // Initialize divisi on page load if kode_petak is selected
                if (kodePetakSelect.value) {
                    updateDivisi(kodePetakSelect.value);
                }
            }

            // Handle delete button click with SweetAlert2 confirmation
            document.getElementById('deleteButton').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const button = this;
                const deleteUrl = button.getAttribute('data-delete-url');
                const kodePetak = button.getAttribute('data-kode-petak');
                const originalText = button.innerHTML;

                // Show confirmation dialog
                Swal.fire({
                    title: 'Konfirmasi Hapus Data',
                    html: `Anda akan menghapus data dengan kode petak: <strong>${kodePetak}</strong><br><br>
                           <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Data yang dihapus tidak dapat dikembalikan.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
                    cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menghapus...';

                        // Get CSRF token from meta tag
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                                    // Redirect to index page
                                    window.location.href = "{{ route('foreman-sub-blocks.index') }}";
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
                            button.disabled = false;
                            button.innerHTML = originalText;
                        });
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection
