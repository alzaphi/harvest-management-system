@extends('layouts.master')

@section('title', 'Edit Kendaraan')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>

    .select2-container .select2-selection--single {
        height: 38px !important;
        padding: 5px 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        padding: 6px;
    }
    
    .btn, .btn-secondary {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        margin-right: 0.5rem;
        text-decoration: none;
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
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group:last-child {
        margin-top: 1.5rem;
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Edit Kendaraan</h2>
    
    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="kode_vendor" class="form-label">Kode Vendor</label>
            <select name="kode_vendor" id="kode_vendor" class="form-select select2 @error('kode_vendor') is-invalid @enderror" required onchange="updateNamaVendor(this)">
                <option value="">Pilih Kode Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->kode_vendor }}" 
                            data-nama="{{ $vendor->nama_vendor }}" 
                            {{ old('kode_vendor', $vehicle->kode_vendor) == $vendor->kode_vendor ? 'selected' : '' }}>
                        {{ $vendor->kode_vendor }} - {{ $vendor->nama_vendor }}
                    </option>
                @endforeach
            </select>
            @error('kode_vendor')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="nama_vendor" class="form-label">Nama Vendor</label>
            <input type="text" name="nama_vendor" id="nama_vendor" class="form-input @error('nama_vendor') is-invalid @enderror" 
                   value="{{ old('nama_vendor', $vehicle->vendor ? $vehicle->vendor->nama_vendor : '') }}" 
                   readonly 
                   style="background-color: #f8fafc; color: #1f2937;">
            @error('nama_vendor')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="kode_lambung" class="form-label">Kode Lambung</label>
            <input type="text" name="kode_lambung" id="kode_lambung" 
                   class="form-input @error('kode_lambung') is-invalid @enderror" 
                   value="{{ old('kode_lambung', $vehicle->kode_lambung) }}" 
                   readonly
                   style="background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;">
            @error('kode_lambung')
            <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">Kode lambung tidak dapat diubah</small>
        </div>

        <div class="form-group">
            <label for="plat_nomor" class="form-label">No Polisi</label>
            <input 
                type="text" 
                name="plat_nomor_display" 
                id="plat_nomor" 
                class="form-input @error('plat_nomor') is-invalid @enderror" 
                value="{{ old('plat_nomor', $vehicle->plat_nomor) }}" 
                placeholder="Contoh: B 1234 ABC"
                title="Format: XX - XXXX - XXX (contoh: B 1234 ABC)"
                required
                style="text-transform: uppercase;">
            <input type="hidden" name="plat_nomor" id="plat_nomor_raw" value="{{ old('plat_nomor', $vehicle->plat_nomor) }}">
            @error('plat_nomor')
            <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">Format: XX - XXXX - XXX (contoh: B - 1234 - ABC)</small>
        </div>

        <div class="form-group">
            <label for="jenis_unit_id" class="form-label">Jenis Unit</label>
            <select name="jenis_unit_id" id="jenis_unit_id" class="form-select @error('jenis_unit_id') is-invalid @enderror" required>
                <option value="">Pilih Jenis Unit</option>
                @foreach($jenisUnits as $id => $namaUnit)
                    <option value="{{ $id }}" {{ old('jenis_unit_id', $vehicle->jenis_unit_id) == $id ? 'selected' : '' }}>
                        {{ $namaUnit }}
                    </option>
                @endforeach
            </select>
            @error('jenis_unit_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Cari kode atau nama vendor',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 1,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                return data;
            }
            return null;
        }
    });

    // Format plat nomor as user types
    function formatPlatNomor(input) {
        // Get current cursor position
        const cursorPosition = input.selectionStart;
        
        // Get the raw value (without formatting)
        let rawValue = input.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        
        // Format the display value (with hyphens)
        let displayFormatted = '';
        // Format the stored value (with spaces only)
        let storedFormatted = '';
        
        // Add first part (1-2 letters for kode wilayah)
        const kodeWilayah = rawValue.match(/^[A-Z]{1,2}/);
        if (kodeWilayah) {
            displayFormatted += kodeWilayah[0] + ' - ';
            storedFormatted += kodeWilayah[0] + ' ';
            rawValue = rawValue.substring(kodeWilayah[0].length);
        }
        
        // Add middle part (up to 4 digits)
        const nomor = rawValue.match(/^\d{1,4}/);
        if (nomor) {
            displayFormatted += nomor[0] + ' - ';
            storedFormatted += nomor[0] + ' ';
            rawValue = rawValue.substring(nomor[0].length);
        }
        
        // Add last part (up to 3 letters for kode seri)
        const kodeSeri = rawValue.match(/^[A-Z]{1,3}/);
        if (kodeSeri) {
            displayFormatted += kodeSeri[0];
            storedFormatted += kodeSeri[0];
        }
        
        // Store the formatted value with spaces only in the hidden field
        $('#plat_nomor_raw').val(storedFormatted.trim());
        
        return { value: displayFormatted, cursor: cursorPosition };
    }

    // Format existing plat nomor on page load
    const initialPlatNomor = $('#plat_nomor_raw').val();
    if (initialPlatNomor) {
        const formatted = formatPlatNomor({ 
            value: initialPlatNomor, 
            selectionStart: 0 
        });
        $('#plat_nomor').val(formatted.value);
    }

    $('#plat_nomor').on('input', function(e) {
        // Don't format if user is deleting text
        if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
            // Still need to update the raw value
            const rawValue = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            $('#plat_nomor_raw').val(rawValue);
            return;
        }
        
        const result = formatPlatNomor(this);
        this.value = result.value;
        
        // Restore cursor position
        this.setSelectionRange(result.cursor, result.cursor);
    });
});

// Function to update vendor name when vendor is selected
function updateNamaVendor(select) {
    const selectedOption = select.options[select.selectedIndex];
    const namaVendor = selectedOption.getAttribute('data-nama') || '';
    document.getElementById('nama_vendor').value = namaVendor;
}

// Initialize vendor name on page load
document.addEventListener('DOMContentLoaded', function() {
    const vendorSelect = document.getElementById('kode_vendor');
    if (vendorSelect) {
        updateNamaVendor(vendorSelect);
    }
});
</script>
@endpush

@endsection
