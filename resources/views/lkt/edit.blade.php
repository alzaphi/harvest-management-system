@extends('layouts.master')

@section('page-title', 'Edit Lembar Kerja Tebang (LKT)')

@php
    $header = 'Lembar Kerja Tebang';
    $breadcrumb = [
        ['title' => 'Lembar Kerja Tebang', 'url' => route('lkt.index')],
        ['title' => 'Edit Lembar Kerja Tebang', 'url' => route('lkt.edit', $lkt->id)]
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 42px;
        padding: 8px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2 class="mb-4">Edit Data LKT</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lkt.update', $lkt->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Kode LKT</label>
                <input type="text" name="kode_lkt" value="{{ $lkt->kode_lkt }}" readonly class="form-input w-full">
            </div>

            <div>
                <label class="block font-medium">Kode SPT</label>
                <select name="kode_spt" id="kode_spt" class="form-select w-full select2-spt" required>
                    <option value="">-- Pilih Kode SPT --</option>
                    @foreach($spts as $spt)
                        <option value="{{ $spt->kode_spt }}" 
                                data-vendor="{{ $spt->vendor->kode_vendor ?? '' }} / {{ $spt->vendor->nama_vendor ?? '' }}"
                                data-petak="{{ $spt->kode_petak }}"
                                {{ $lkt->kode_spt == $spt->kode_spt ? 'selected' : '' }}>
                            {{ $spt->kode_spt }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium">Tanggal Tebang</label>
                <input type="date" name="tanggal_tebang" class="form-input w-full" value="{{ $lkt->tanggal_tebang }}" required>
            </div>

            <div>
                <label class="block font-medium">Kode Petak</label>
                <input type="text" name="kode_petak" id="kode_petak" class="form-input w-full" value="{{ $lkt->kode_petak }}" readonly>
            </div>

            <div>
                <label class="block font-medium">Vendor Tebang</label>
                <input type="text" name="kode_vendor_tebang" id="kode_vendor_tebang" class="form-input w-full" value="{{ $lkt->kode_vendor_tebang }}" readonly>
            </div>

            <div>
                <label class="block font-medium">Vendor Angkut</label>
                <select name="kode_vendor_angkut" id="kode_vendor_angkut" class="form-select w-full select2-vendor">
                    <option value="">-- Pilih Vendor Angkut --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->kode_vendor }}" 
                                data-nama="{{ $vendor->nama_vendor }}"
                                {{ $vendor->kode_vendor == $lkt->kode_vendor_angkut ? 'selected' : '' }}>
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
                                data-vendor="{{ $driver->nama_vendor }}"
                                {{ $driver->kode_lambung == $lkt->kode_driver ? 'selected' : '' }}>
                            {{ $driver->kode_lambung }} / {{ $driver->plat_nomor }} / {{ $driver->nama_vendor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium">Tarif Zona Angkutan</label>
                <input type="text" name="tarif_zona_angkutan" id="tarif_zona_angkutan" class="form-input w-full" value="{{ $lkt->tarif_zona_angkutan }}" readonly>
            </div>

            <div>
                <label class="block font-medium">Jenis Tebangan</label>
                <input type="text" name="jenis_tebangan" id="jenis_tebangan" class="form-input w-full" value="{{ $lkt->jenis_tebangan }}" readonly>
            </div>
        </div>

        <div class="mt-6">
            <label class="block font-medium">Catatan</label>
            <textarea name="catatan" class="form-textarea w-full">{{ $lkt->catatan }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <label class="block font-medium">Dibuat Oleh</label>
                <input type="text" name="dibuat_oleh" value="{{ $lkt->dibuat_oleh ?? 'Mandor' }}" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Diperiksa 1 Oleh</label>
                <input type="text" name="diperiksa_oleh" value="{{ $lkt->diperiksa_oleh ?? 'Asst. Divisi Plantation' }}" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Diperiksa 2 Oleh</label>
                <input type="text" name="disetujui_oleh" value="{{ $lkt->disetujui_oleh ?? 'Asst. Manager Plantation' }}" readonly class="form-input w-full bg-gray-100">
            </div>

            <div>
                <label class="block font-medium">Ditimbang Oleh</label>
                <input type="text" name="ditimbang_oleh" value="{{ $lkt->ditimbang_oleh ?? 'Petugas Timbangan' }}" readonly class="form-input w-full bg-gray-100">
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <a href="{{ route('lkt.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        $('#kode_vendor_tebang').val($option.data('vendor') || '');
        $('#kode_petak').val($option.data('petak') || '');
        
        // Also fetch detailed data from the server
        const kodeSPT = $(this).val();
        if (kodeSPT) {
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
                        $('#kode_vendor_tebang').val(data.kode_vendor_tebang || '');

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
        $('#kode_vendor_tebang').val('');
        $('#kode_petak').val('');
        $('#tarif_zona_angkutan').val('');
        $('#jenis_tebangan').val('');
    }
});
</script>
@endpush
