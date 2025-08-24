@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Style untuk Select2 */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
    padding-left: 0;
    color: #374151;
}

/* Dropdown styles */
.select2-container--default .select2-dropdown {
    border: 1px solid #3b82f6;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2), 0 2px 4px -1px rgba(59, 130, 246, 0.1);
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
    margin-bottom: 5px;
}

/* Dropdown items */
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f3f4f6;
    color: #1f2937;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6;
    color: white;
}

/* Arrow color */
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #6b7280 transparent;
}
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Tambah Kendaraan</h2>
    
    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('vehicles.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="kode_vendor" class="form-label">Kode Vendor</label>
            <select name="kode_vendor" id="kode_vendor" class="form-select select2 @error('kode_vendor') is-invalid @enderror" required onchange="updateNamaVendor(this)">
                <option value="">Pilih Kode Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->kode_vendor }}" 
                            data-nama="{{ $vendor->nama_vendor }}" 
                            {{ old('kode_vendor') == $vendor->kode_vendor ? 'selected' : '' }}>
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
                   value="" readonly style="background-color: #f8fafc; color: #1f2937;">
            @error('nama_vendor')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="kode_lambung" class="form-label">Kode Lambung</label>
            <input type="text" name="kode_lambung" id="kode_lambung" 
                   class="form-input @error('kode_lambung') is-invalid @enderror" 
                   value="{{ $nextKodeLambung }}" readonly
                   style="background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;">
            @error('kode_lambung')
            <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">Kode lambung akan di-generate otomatis</small>
        </div>

        <div class="form-group">
            <label for="plat_nomor" class="form-label">No Polisi</label>
            <input 
                type="text" 
                name="plat_nomor_display" 
                id="plat_nomor" 
                class="form-input @error('plat_nomor') is-invalid @enderror" 
                value="{{ old('plat_nomor') }}" 
                placeholder="Contoh: B 1234 ABC"
                title="Format: XX - XXXX - XXX (contoh: B 1234 ABC)"
                required
                style="text-transform: uppercase;">
            <input type="hidden" name="plat_nomor" id="plat_nomor_raw">
            @error('plat_nomor')
            <div class="text-danger">{{ $message }}</div>
            @enderror
            <small class="text-gray-500">Format: XX - XXXX - XXX (contoh: B 1234 ABC)</small>
        </div>

        <div class="form-group">
            <label for="jenis_unit_id" class="form-label">Jenis Unit</label>
            <select name="jenis_unit_id" id="jenis_unit_id" class="form-select @error('jenis_unit_id') is-invalid @enderror" required>
                <option value="">Pilih Jenis Unit</option>
                @foreach($jenisUnits as $id => $namaUnit)
                    <option value="{{ $id }}" {{ old('jenis_unit_id') == $id ? 'selected' : '' }}>
                        {{ $namaUnit }}
                    </option>
                @endforeach
            </select>
            @error('jenis_unit_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Select2 initialization
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
        document.getElementById('plat_nomor_raw').value = storedFormatted.trim();
        
        return { value: displayFormatted, cursor: cursorPosition };
    }

    $('#plat_nomor').on('input', function(e) {
        // Don't format if user is deleting text
        if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') {
            // Still need to update the raw value
            const rawValue = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            document.getElementById('plat_nomor_raw').value = rawValue;
            return;
        }
        
        const result = formatPlatNomor(this);
        this.value = result.value;
        
        // Restore cursor position
        this.setSelectionRange(result.cursor, result.cursor);
    });
});

// Inisialisasi nama vendor saat page load
document.addEventListener('DOMContentLoaded', function() {
    const kodeVendorSelect = document.getElementById('kode_vendor');
    if (kodeVendorSelect) {
        updateNamaVendor(kodeVendorSelect);
    }
});

function updateNamaVendor(selectElement) {
    const namaVendorInput = document.getElementById('nama_vendor');
    let selectedOption;
    
    // Handle both regular select and Select2
    if (selectElement.selectedIndex !== undefined) {
        selectedOption = selectElement.options[selectElement.selectedIndex];
    } else if (selectElement.length > 0) {
        // This is for Select2
        const selectedData = $(selectElement).select2('data');
        if (selectedData && selectedData[0]) {
            const selectedText = selectedData[0].text;
            const vendorCode = selectedText.split(' - ')[0];
            selectedOption = { 
                getAttribute: function(attr) {
                    if (attr === 'data-nama') {
                        return selectedText.substring(selectedText.indexOf('-') + 2);
                    }
                    return null;
                }
            };
        }
    }
    
    if (selectedOption) {
        const namaVendor = selectedOption.getAttribute('data-nama');
        if (namaVendor) {
            namaVendorInput.value = namaVendor;
            namaVendorInput.style.color = '#1f2937';
            return;
        }
    }
    
    namaVendorInput.value = '';
    namaVendorInput.style.color = '#6b7280';
}
</script>
@endpush
@endsection