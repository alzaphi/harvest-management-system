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
    .form-input, .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-input:focus, .form-select:focus {
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
    .menu-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    .tab-button1 {
        padding: 0.5rem 1rem;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-bottom: none;
        margin-right: 0.25rem;
        text-decoration: none;
        color: #6b7280;
        border-radius: 0.375rem 0.375rem 0 0;
    }
    .tab-button1.active, .tab-button1:hover {
        background-color: #fff;
        color: #3b82f6;
        border-bottom-color: #fff;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Tambah Status Sub Block</h2>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Sukses!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validasi Gagal</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('status-sub-blocks.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0">Informasi Sub Block</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="kode_petak" class="form-label">Kode Petak <span class="text-danger">*</span></label>
                            <select name="kode_petak" id="kode_petak" class="form-select" required>
                                <option value="">Pilih Kode Petak</option>
                                @foreach($subBlocks as $subBlock)
                                    <option value="{{ $subBlock->kode_petak }}"
                                            data-estate="{{ $subBlock->estate }}"
                                            data-divisi="{{ $subBlock->divisi }}"
                                            data-luas="{{ $subBlock->luas_area }}"
                                            data-geom="{{ $subBlock->geom }}">
                                        {{ $subBlock->kode_petak }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Luas Area (ha)</label>
                            <input type="text" id="luas_area" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Estate</label>
                            <input type="text" id="estate" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Divisi</label>
                            <input type="text" id="divisi" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0">Status Sub Block</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Pilih Status</option>
                                <option value="Planned Cutting">Planned Cutting</option>
                                <option value="Already Cut Down">Already Cut Down</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="luas_status" class="form-label">Luas Status (ha) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="luas_status" id="luas_status" class="form-control" required>
                                <span class="input-group-text">ha</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="tanggal_update" class="form-label">Tanggal Update <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_update" id="tanggal_update" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <div class="form-check mt-4 pt-2">
                                <input type="checkbox" name="aktif" id="aktif" class="form-check-input" checked>
                                <label for="aktif" class="form-check-label">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('status-sub-blocks.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kodePetakSelect = document.getElementById('kode_petak');
    const estateInput = document.getElementById('estate');
    const divisiInput = document.getElementById('divisi');
    const luasAreaInput = document.getElementById('luas_area');
    const luasStatusInput = document.getElementById('luas_status');
    const geomInput = document.createElement('input');
    geomInput.type = 'hidden';
    geomInput.name = 'geom';
    document.querySelector('form').appendChild(geomInput);

    // Update form fields when kode_petak is selected
    kodePetakSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            estateInput.value = selectedOption.dataset.estate || '';
            divisiInput.value = selectedOption.dataset.divisi || '';
            luasAreaInput.value = selectedOption.dataset.luas ? parseFloat(selectedOption.dataset.luas).toFixed(2) : '';
            geomInput.value = selectedOption.dataset.geom || '';

            // Auto-fill luas_status with luas_area
            if (luasAreaInput.value && !luasStatusInput.value) {
                luasStatusInput.value = parseFloat(luasAreaInput.value).toFixed(2);
            }
        } else {
            estateInput.value = '';
            divisiInput.value = '';
            luasAreaInput.value = '';
            luasStatusInput.value = '';
        }
    });

    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_update').value = today;
});
</script>
@endpush

@endsection
