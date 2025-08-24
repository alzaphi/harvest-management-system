@extends('layouts.master')

@php
    $header = 'Edit Vendor';
    $breadcrumb = [
        ['title' => 'List Vendor', 'url' => route('vendor.index')],
        ['title' => 'Edit Vendor']
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<style>
    #vendorCodesContainer {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .code-group {
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #f9fafb;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Edit Vendor</h2>
    <form action="{{ route('vendor.update', $vendor->id) }}" method="POST" id="vendorForm" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="vendor_type" id="vendorType" value="{{ $vendor->jenis_vendor }}">

        <!-- Kode Vendor Fields -->
        <div id="vendorCodesContainer">
            <!-- Kode Vendor Angkut -->
            <div id="angkutCodeGroup" class="code-group" style="display: none;">
                <label for="kode_vendor_angkut">Kode Vendor Angkut</label>
                <input type="text" name="kode_vendor_angkut" id="kode_vendor_angkut" class="form-input" 
                    value="{{ old('kode_vendor_angkut', $vendor->kode_vendor_angkut) }}" readonly>
            </div>

            <!-- Kode Vendor Tebang -->
            <div id="tebangCodeGroup" class="code-group" style="display: none;">
                <label for="kode_vendor_tebang">Kode Vendor Tebang</label>
                <input type="text" name="kode_vendor_tebang" id="kode_vendor_tebang" class="form-input" 
                    value="{{ old('kode_vendor_tebang', $vendor->kode_vendor_tebang) }}" readonly>
            </div>
        </div>

        <div class="form-group">
            <label for="nama_vendor" class="form-label">Nama Vendor</label>
            <input type="text" name="nama_vendor" id="nama_vendor" 
                   class="form-input @error('nama_vendor') border-red-500 @enderror" 
                   value="{{ old('nama_vendor', $vendor->nama_vendor) }}" required>
            @error('nama_vendor')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="no_hp" class="form-label">No HP</label>
            <input type="text" name="no_hp" id="no_hp" 
                   class="form-input @error('no_hp') border-red-500 @enderror" 
                   value="{{ old('no_hp', $vendor->no_hp) }}" required>
            @error('no_hp')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Vendor -->
        <div class="form-group">
            <label for="jenis_vendor" class="form-label">Jenis Vendor</label>
            <select name="jenis_vendor" id="jenis_vendor" class="form-select @error('jenis_vendor') border-red-500 @enderror" required>
                <option value="" disabled selected>Pilih Jenis Vendor</option>
                <option value="angkut" {{ old('jenis_vendor', $vendor->jenis_vendor) == 'angkut' ? 'selected' : '' }}>Vendor Angkut</option>
                <option value="tebang" {{ old('jenis_vendor', $vendor->jenis_vendor) == 'tebang' ? 'selected' : '' }}>Vendor Tebang</option>
                <option value="both" {{ old('jenis_vendor', $vendor->jenis_vendor) == 'both' ? 'selected' : '' }}>Vendor Angkut & Tebang</option>
            </select>
            @error('jenis_vendor')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') border-red-500 @enderror" required>
                <option value="" disabled selected>Pilih Status</option>
                <option value="Aktif" {{ old('status', $vendor->status) === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ old('status', $vendor->status) === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jumlah Tenaga Kerja -->
        <div class="form-group">
            <label for="jumlah_tenaga_kerja" class="form-label">Jumlah Tenaga Kerja</label>
            <input type="number" name="jumlah_tenaga_kerja" id="jumlah_tenaga_kerja" min="0" 
                   class="form-input @error('jumlah_tenaga_kerja') border-red-500 @enderror" 
                   value="{{ old('jumlah_tenaga_kerja', $vendor->jumlah_tenaga_kerja ?? 0) }}" required>
            @error('jumlah_tenaga_kerja')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nomor Rekening -->
        <div class="form-group">
            <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" id="nomor_rekening" class="form-input @error('nomor_rekening') border-red-500 @enderror" 
                   value="{{ old('nomor_rekening', $vendor->nomor_rekening) }}" required>
            @error('nomor_rekening')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="nama_bank" class="form-label">Nama Bank</label>
            <input type="text" name="nama_bank" id="nama_bank" class="form-input @error('nama_bank') border-red-500 @enderror" 
                   value="{{ old('nama_bank', $vendor->nama_bank) }}" required>
            @error('nama_bank')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('vendorForm');
        const select = document.getElementById('jenis_vendor');
        const angkutGroup = document.getElementById('angkutCodeGroup');
        const tebangGroup = document.getElementById('tebangCodeGroup');
        const kodeAngkutInput = document.getElementById('kode_vendor_angkut');
        const kodeTebangInput = document.getElementById('kode_vendor_tebang');
        const vendorTypeInput = document.getElementById('vendorType');
        
        // Store the original values
        const originalKodeAngkut = kodeAngkutInput.value;
        const originalKodeTebang = kodeTebangInput.value;
        const originalJenisVendor = '{{ $vendor->jenis_vendor }}';
        
        // Initialize the display based on current vendor type
        function initializeVendorType() {
            const currentType = '{{ $vendor->jenis_vendor }}';
            
            if (currentType === 'angkut' || currentType === 'both') {
                angkutGroup.style.display = 'block';
            }
            
            if (currentType === 'tebang' || currentType === 'both') {
                tebangGroup.style.display = 'block';
            }
            
            // Set the initial values
            vendorTypeInput.value = currentType;
        }

        function toggleVendorCodes() {
            const selectedValue = select.value;
            
            // Show/hide relevant fields based on selection
            angkutGroup.style.display = (selectedValue === 'angkut' || selectedValue === 'both') ? 'block' : 'none';
            tebangGroup.style.display = (selectedValue === 'tebang' || selectedValue === 'both') ? 'block' : 'none';
            
            // Update the hidden vendor type field
            vendorTypeInput.value = selectedValue;
            
            // Handle code generation when type changes
            if (selectedValue === 'angkut') {
                // If switching to angkut, restore original or use current angkut code
                kodeAngkutInput.value = originalKodeAngkut || kodeAngkutInput.value;
            } else if (selectedValue === 'tebang') {
                // If switching to tebang, restore original or use current tebang code
                kodeTebangInput.value = originalKodeTebang || kodeTebangInput.value;
            } else if (selectedValue === 'both') {
                // If switching to both, ensure both codes are set
                kodeAngkutInput.value = originalKodeAngkut || kodeAngkutInput.value;
                kodeTebangInput.value = originalKodeTebang || kodeTebangInput.value;
            }
        }

        // Form validation
        if (form) {
            form.addEventListener('submit', function(e) {
                const jenisVendor = select ? select.value : '';
                const jumlahTenagaKerja = document.getElementById('jumlah_tenaga_kerja') ? document.getElementById('jumlah_tenaga_kerja').value : '';
                const nomorRekening = document.getElementById('nomor_rekening') ? document.getElementById('nomor_rekening').value : '';
                const namaBank = document.getElementById('nama_bank') ? document.getElementById('nama_bank').value : '';
                
                // Clear previous error messages
                document.querySelectorAll('.error-message').forEach(el => el.remove());
                
                let isValid = true;
                
                if (!jenisVendor) {
                    showError(select, 'Jenis vendor harus dipilih');
                    isValid = false;
                }
                
                // Only validate bank details if they are required
                if (nomorRekening && !namaBank.trim()) {
                    showError(document.getElementById('nama_bank'), 'Nama bank harus diisi');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
        
        function showError(input, message) {
            const error = document.createElement('p');
            error.className = 'text-red-500 text-sm mt-1 error-message';
            error.textContent = message;
            input.parentNode.insertBefore(error, input.nextSibling);
            input.classList.add('border-red-500');
        }

        // Initial setup
        initializeVendorType();

        // Add event listener for changes
        if (select) {
            select.addEventListener('change', toggleVendorCodes);
        }
        
        // Trigger change event to set initial state
        if (select && select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush

@endsection
