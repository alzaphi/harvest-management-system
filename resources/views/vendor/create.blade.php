@extends('layouts.master')

@php
    $header = 'Vendor Baru';
    $breadcrumb = [
        ['title' => 'List Vendor', 'url' => route('vendor.index')],
        ['title' => 'Vendor Baru']
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
    <h2>Tambah Vendor Baru</h2>
    <form action="{{ route('vendor.store') }}" method="POST" id="vendorForm" novalidate>
        @csrf
        <input type="hidden" name="vendor_type" id="vendorType" value="angkut">



        <!-- Kode Vendor Fields -->
        <div id="vendorCodesContainer">
            <!-- Kode Vendor Angkut -->
            <div id="angkutCodeGroup" class="code-group">
                <label for="kode_vendor_angkut" class="form-label">Kode Vendor Angkut</label>
                <input type="text" name="kode_vendor_angkut" id="kode_vendor_angkut" class="form-input" value="{{ $newKode }}" readonly>
                @error('kode_vendor_angkut')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Kode Vendor Tebang -->
            <div id="tebangCodeGroup" class="code-group" style="display: none;">
                <label for="kode_vendor_tebang" class="form-label">Kode Vendor Tebang</label>
                <input type="text" name="kode_vendor_tebang" id="kode_vendor_tebang" class="form-input" value="{{ $newKodeTebang }}" readonly>
                @error('kode_vendor_tebang')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Nama Vendor -->
        <div class="form-group">
            <label for="nama_vendor" class="form-label">Nama Vendor</label>
            <input type="text" name="nama_vendor" id="nama_vendor" class="form-input" value="{{ old('nama_vendor') }}">
            @error('nama_vendor')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- No HP -->
        <div class="form-group">
            <label for="no_hp" class="form-label">No HP </label>
            <input type="text" name="no_hp" id="no_hp" class="form-input @error('no_hp') border-red-500 @enderror"
                   value="{{ old('no_hp') }}" required>
            @error('no_hp')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Vendor -->
        <div class="form-group">
            <label for="jenis_vendor" class="form-label">Jenis Vendor</label>
            <select name="jenis_vendor" id="jenis_vendor" class="form-select" required>
                <option value="">Pilih Jenis Vendor</option>
                <option value="angkut" {{ old('jenis_vendor') == 'angkut' ? 'selected' : '' }}>Vendor Angkut</option>
                <option value="tebang" {{ old('jenis_vendor') == 'tebang' ? 'selected' : '' }}>Vendor Tebang</option>
                <option value="both" {{ old('jenis_vendor') == 'both' ? 'selected' : '' }}>Vendor Angkut & Tebang</option>
            </select>
            @error('jenis_vendor')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Status -->
        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') border-red-500 @enderror" required>
                <option value="" disabled selected>Pilih Status</option>
                <option value="Aktif" {{ old('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ old('status') === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
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
                   value="{{ old('jumlah_tenaga_kerja', 0) }}" required>
            @error('jumlah_tenaga_kerja')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nomor Rekening -->
        <div class="form-group">
            <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
            <input type="text" name="nomor_rekening" id="nomor_rekening" class="form-input @error('nomor_rekening') border-red-500 @enderror"
                   value="{{ old('nomor_rekening') }}" required>
            @error('nomor_rekening')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Bank -->
        <div class="form-group">
            <label for="nama_bank" class="form-label">Nama Bank</label>
            <input type="text" name="nama_bank" id="nama_bank" class="form-input @error('nama_bank') border-red-500 @enderror"
                   value="{{ old('nama_bank') }}" required>
            @error('nama_bank')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleKodeVendorFields() {
        const jenisVendor = document.getElementById('jenis_vendor').value;
        const angkutGroup = document.getElementById('angkutCodeGroup');
        const tebangGroup = document.getElementById('tebangCodeGroup');

        // Reset required attribute
        const kodeAngkut = document.getElementById('kode_vendor_angkut');
        const kodeTebang = document.getElementById('kode_vendor_tebang');

        kodeAngkut.required = false;
        kodeTebang.required = false;

        if (jenisVendor === 'angkut') {
            angkutGroup.style.display = 'block';
            tebangGroup.style.display = 'none';
            kodeAngkut.required = true;
        } else if (jenisVendor === 'tebang') {
            angkutGroup.style.display = 'none';
            tebangGroup.style.display = 'block';
            kodeTebang.required = true;
        } else if (jenisVendor === 'both') {
            angkutGroup.style.display = 'block';
            tebangGroup.style.display = 'block';
            kodeAngkut.required = true;
            kodeTebang.required = true;
        } else {
            angkutGroup.style.display = 'none';
            tebangGroup.style.display = 'none';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
        toggleKodeVendorFields();

        // Add change event listener to jenis_vendor select
        document.getElementById('jenis_vendor').addEventListener('change', function() {
            toggleKodeVendorFields();

            // Reset validation messages
            const errorElements = document.querySelectorAll('.text-danger');
            errorElements.forEach(el => el.remove());
        });

        // Check if there's a new vendor code from the server
        @if(session('kode_vendor_angkut'))
            document.getElementById('kode_vendor_angkut').value = '{{ session('kode_vendor_angkut') }}';

            // Show warning message if exists
            @if(session('warning'))
                alert('{{ session('warning') }}');
            @endif
        @endif
    });
</script>
@endpush
