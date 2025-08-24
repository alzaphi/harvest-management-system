@extends('layouts.master')

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
    @php
        $header = 'Tambah Foreman Sub Block';
        $breadcrumb = [
            ['title' => 'List Foreman Sub Block', 'url' => route('foreman-sub-blocks.index')],
            ['title' => $header]
        ];
    @endphp

    <div class="vendor-container">
        <h2>Tambah Foreman Sub Block</h2>

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

    <div id="alert-container"></div>
    
    <form id="foremanSubBlockForm" class="needs-validation" novalidate>
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0">Data Foreman Sub Block</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_petak" class="form-label">Kode Petak</label>
                            <select class="form-select @error('kode_petak') is-invalid @enderror" id="kode_petak" name="kode_petak" required>
                                <option value="">Pilih Kode Petak</option>
                                @foreach($subBlocks as $subBlock)
                                    <option value="{{ $subBlock->kode_petak }}"
                                        data-divisi="{{ $subBlock->divisi }}"
                                        {{ old('kode_petak') == $subBlock->kode_petak ? 'selected' : '' }}>
                                        {{ $subBlock->kode_petak }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="kode_petak_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="divisi" class="form-label">Divisi</label>
                            <input type="text" class="form-control bg-light @error('divisi') is-invalid @enderror" id="divisi" name="divisi" value="{{ old('divisi') }}" required readonly>
                            <div class="invalid-feedback" id="divisi_error"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_mandor" class="form-label">Kode Mandor</label>
                            <input type="text" class="form-control @error('kode_mandor') is-invalid @enderror" id="kode_mandor" name="kode_mandor" value="{{ old('kode_mandor') }}" required maxlength="10" readonly>
                            <div class="invalid-feedback" id="kode_mandor_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mandor" class="form-label">Nama Mandor</label>
                            <select class="form-select @error('nama_mandor') is-invalid @enderror" id="nama_mandor" name="nama_mandor" required>
                                <option value="">Pilih Nama Mandor</option>
                                @foreach($foremanNames as $foreman)
                                    <option value="{{ $foreman->nama_mandor }}"
                                        data-kode-mandor="{{ $foreman->kode_mandor }}"
                                        {{ old('nama_mandor') == $foreman->nama_mandor ? 'selected' : '' }}>
                                        {{ $foreman->nama_mandor }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="nama_mandor_error"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('foreman-sub-blocks.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save me-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-fill divisi when kode_petak is selected
        const kodePetakSelect = document.getElementById('kode_petak');
        const divisiInput = document.getElementById('divisi');
        const namaMandorSelect = document.getElementById('nama_mandor');
        const kodeMandorInput = document.getElementById('kode_mandor');
        const form = document.getElementById('foremanSubBlockForm');
        const submitBtn = document.getElementById('submitBtn');
        const alertContainer = document.getElementById('alert-container');
        
        // Function to show alert message using SweetAlert2
        function showAlert(title, message, type = 'success') {
            return Swal.fire({
                title: type === 'success' ? 'Berhasil!' : 'Error!',
                text: message,
                icon: type,
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset all error messages
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            
            const formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
            
            // Show loading state with SweetAlert2
            Swal.fire({
                title: 'Menyimpan Data',
                text: 'Sedang memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send AJAX request
            fetch('{{ route('foreman-sub-blocks.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Close loading dialog
                    Swal.close();
                    
                    // Show success message and redirect
                    showAlert('Berhasil!', 'Data berhasil disimpan', 'success').then(() => {
                        window.location.href = data.redirect;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                if (error.errors) {
                    // Handle validation errors
                    Object.entries(error.errors).forEach(([field, messages]) => {
                        const input = document.querySelector(`[name="${field}"]`);
                        const errorElement = document.getElementById(`${field}_error`);
                        
                        if (input) {
                            input.classList.add('is-invalid');
                        }
                        
                        if (errorElement) {
                            errorElement.textContent = messages[0];
                        }
                    });
                    
                    showAlert('Error!', 'Terdapat kesalahan pada form. Silakan periksa kembali.', 'error');
                } else {
                    showAlert('Error!', error.message || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan';
            });
        });
        
        // Auto-fill divisi when kode_petak is selected
        kodePetakSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                divisiInput.value = selectedOption.dataset.divisi || '';
            }
        });
        
        // Auto-fill kode_mandor when nama_mandor is selected
        namaMandorSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                kodeMandorInput.value = selectedOption.dataset.kodeMandor || '';
            }
        });
        
        // Initialize form with any existing values
        if (kodePetakSelect.value) {
            divisiInput.value = kodePetakSelect.options[kodePetakSelect.selectedIndex].dataset.divisi || '';
        }
        
        if (namaMandorSelect.value) {
            kodeMandorInput.value = namaMandorSelect.options[namaMandorSelect.selectedIndex].dataset.kodeMandor || '';
        }

        if (kodePetakSelect && divisiInput) {
            kodePetakSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const divisi = selectedOption.getAttribute('data-divisi');
                    divisiInput.value = divisi || '';
                } else {
                    divisiInput.value = '';
                }
            });

            // Trigger change event on page load if there's a selected value
            if (kodePetakSelect.value) {
                kodePetakSelect.dispatchEvent(new Event('change'));
            }
        }

        // Auto-fill kode_mandor when nama_mandor is selected
        if (namaMandorSelect && kodeMandorInput) {
            namaMandorSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const kodeMandor = selectedOption.getAttribute('data-kode-mandor');
                    kodeMandorInput.value = kodeMandor || '';
                } else {
                    kodeMandorInput.value = '';
                }
            });

            // Trigger change event on page load if there's a selected value
            if (namaMandorSelect.value) {
                namaMandorSelect.dispatchEvent(new Event('change'));
            }
        }

        // Enable form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    });
</script>
@endpush

@endsection
