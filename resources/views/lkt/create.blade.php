@extends('layouts.master')

@section('page-title', 'Tambah Lembar Kerja Tebang (LKT)')

@php
    $header = 'Lembar Kerja Tebang';
    $breadcrumb = [
        ['title' => 'Lembar Kerja Tebang', 'url' => route('lkt.index')],
        ['title' => 'Tambah Lembar Kerja Tebang', 'url' => route('lkt.create')]
    ];
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    
.vendor-container {
    padding-top: 1rem !important;
}
.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;  
    border-radius: 0.375rem;
    background-color: #fff;
    color: #374151;
    font-size: 0.875rem;
    line-height: 1.5;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input[readonly], .form-select[readonly] {
    background-color: #f9fafb;
    cursor: not-allowed;
}

/* Style untuk Select2 */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 6px 12px;
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
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
    padding: 0.5rem 0.75rem;
    margin-bottom: 5px;
}

/* Dropdown items */
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f3f4f6;
    color: #1f2937;
}

/* Style untuk label */
label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

/* Style untuk form group */
.form-group {
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2 class="mb-4" style="color: #1e40af;">Tambah Data LKT</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lkt.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Kode LKT</label>
                <input type="text" name="kode_lkt" value="{{ $kodeLKT }}" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Kode SPT</label>
                <select name="kode_spt" id="kode_spt" class="form-select w-full select2-spt" required>
                    <option value="">-- Pilih Kode SPT --</option>
                    @foreach($spts as $spt)
                        <option value="{{ $spt->kode_spt }}" data-vendor="{{ $spt->vendor->nama_vendor ?? '' }}" 
                            data-petak="{{ $spt->kode_petak }}">{{ $spt->kode_spt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium">Tanggal Tebang</label>
                <input type="date" name="tanggal_tebang" class="form-input w-full" required>
            </div>

            <div>
                <label class="block font-medium">Kode Petak</label>
                <input type="text" name="kode_petak" id="kode_petak" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Vendor Tebang</label>
                <input type="hidden" name="kode_vendor_tebang" id="kode_vendor_tebang_code" value="">
                <input type="text" id="kode_vendor_tebang_display" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Vendor Angkut</label>
                <select name="kode_vendor_angkut" id="kode_vendor_angkut" class="form-select w-full select2-vendor">
                    <option value="">-- Pilih Vendor Angkut --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->kode_vendor }}" 
                                data-nama="{{ $vendor->nama_vendor }}">
                            {{ $vendor->kode_vendor }} / {{ $vendor->nama_vendor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium">Kode Lambung</label>
                <select name="kode_driver" id="kode_driver" class="form-select w-full select2-driver">
                    <option value="">-- Pilih Kode Lambung --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->kode_lambung }}" 
                                data-plat="{{ $driver->plat_nomor }}" 
                                data-vendor="{{ $driver->nama_vendor }}">
                            {{ $driver->kode_lambung }} / {{ $driver->plat_nomor }} / {{ $driver->nama_vendor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium">Tarif Zona Angkutan</label>
                <input type="number" name="tarif_zona_angkutan" id="tarif_zona_angkutan" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Jenis Tebangan</label>
                <input type="text" name="jenis_tebangan" id="jenis_tebangan" readonly class="form-input w-full bg-gray-100">
            </div>
        </div>

        <div class="mt-6">
            <label class="block font-medium">Catatan</label>
            <textarea name="catatan" class="form-textarea w-full" placeholder="Opsional..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <label class="block font-medium">Dibuat Oleh</label>
                <input type="text" name="dibuat_oleh" value="Mandor" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Diperiksa 1 Oleh</label>
                <input type="text" name="diperiksa_oleh" value="Asst. Divisi Plantation" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Diperiksa 2 Oleh</label>
                <input type="text" name="disetujui_oleh" value="Asst. Manager Plantation" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Ditimbang Oleh</label>
                <input type="text" name="ditimbang_oleh" value="Petugas Timbangan" readonly class="form-input w-full bg-gray-100">
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('lkt.index') }}" class="btn btn-secondary ml-2">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for SPT
    $('.select2-spt').select2({
        placeholder: 'Cari kode SPT',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 1,
        templateResult: formatSPT,
        templateSelection: formatSPTSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (data.text === undefined) return null;
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            var vendor = $(data.element).data('vendor')?.toLowerCase() || '';
            return (text.indexOf(searchTerm) > -1 || vendor.indexOf(searchTerm) > -1) ? data : null;
        }
    });

    // Initialize Select2 for Vendor Angkut
    $('.select2-vendor').select2({
        placeholder: 'Cari vendor angkut',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 1,
        templateResult: formatVendor,
        templateSelection: formatVendorSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (data.text === undefined) return null;
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            return text.indexOf(searchTerm) > -1 ? data : null;
        }
    });

    // Initialize Select2 for Kode Lambung
    $('.select2-driver').select2({
        placeholder: 'Cari kode lambung / plat nomor / vendor',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 1,
        templateResult: formatDriver,
        templateSelection: formatDriverSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (data.text === undefined) return null;
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            var plat = $(data.element).data('plat')?.toLowerCase() || '';
            var vendor = $(data.element).data('vendor')?.toLowerCase() || '';
            return (text.indexOf(searchTerm) > -1 || 
                   plat.indexOf(searchTerm) > -1 || 
                   vendor.indexOf(searchTerm) > -1) ? data : null;
        }
    });

    // Format how SPT results are displayed
    function formatSPT(data) {
        if (!data.id) return data.text;
        var vendor = $(data.element).data('vendor') || '';
        var $result = $(
            '<div class="flex justify-between">' +
            '  <span>' + data.text + '</span>' +
            '  <span class="text-gray-500 text-sm">' + vendor + '</span>' +
            '</div>'
        );
        return $result;
    }

    // Format how selected SPT is displayed
    function formatSPTSelection(data) {
        return data.text;
    }

    // Format how vendor results are displayed
    function formatVendor(data) {
        if (!data.id) return data.text;
        var $result = $(
            '<div class="flex justify-between">' +
            '  <span>' + data.text + '</span>' +
            '</div>'
        );
        return $result;
    }

    // Format how selected vendor is displayed
    function formatVendorSelection(data) {
        return data.text;
    }

    // Format how driver results are displayed
    function formatDriver(data) {
        if (!data.id) return data.text;
        var plat = $(data.element).data('plat') || '';
        var vendor = $(data.element).data('vendor') || '';
        return data.text;
    }

    // Format how selected driver is displayed
    function formatDriverSelection(data) {
        return data.text;
    }

    // When SPT is selected, update related fields
    $('#kode_spt').on('select2:select', function(e) {
        var data = e.params.data;
        var $option = $(data.element);
        
        // Update fields from data attributes (quick update)
        $('#kode_vendor_tebang_code').val($option.data('vendor') || '');
        $('#kode_vendor_tebang_display').val($option.data('vendor') || '');
        $('#kode_petak').val($option.data('petak') || '');
        
        // Also fetch detailed data from the server
        const kodeSPT = $(this).val();
        if (kodeSPT) {
            console.log('Mengambil data SPT dengan kode:', kodeSPT);

            fetch(`/lkt/get-spt-data/${kodeSPT}`)
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal mengambil data SPT');
                    }
                    return data;
                })
                .then(data => {
                    console.log('Data SPT:', data);
                    if (data.success) {
                        // Update vendor tebang field
                        $('#kode_vendor_tebang_code').val(data.kode_vendor_tebang || '');
                        $('#kode_vendor_tebang_display').val(data.nama_vendor_tebang || '');

                        // Update kode petak field
                        $('#kode_petak').val(data.kode_petak || '-');

                        // Update tarif zona angkutan field
                        $('#tarif_zona_angkutan').val(data.tarif_zona_angkutan || '1');

                        // Update jenis tebangan field
                        $('#jenis_tebangan').val(data.jenis_tebangan || 'Tebang Biasa');
                    } else {
                        throw new Error(data.message || 'Gagal memuat data SPT');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Terjadi kesalahan saat memuat data SPT');
                    clearFields();
                });
        } else {
            clearFields();
        }
    });

    function clearFields() {
        $('#kode_vendor_tebang_code').val('');
        $('#kode_vendor_tebang_display').val('');
        $('#kode_petak').val('');
        $('#tarif_zona_angkutan').val('');
        $('#jenis_tebangan').val('');
    }
});
</script>
@endpush
