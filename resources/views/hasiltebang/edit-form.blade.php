@php
$header = 'Edit Data Hasil Tebang';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Hasil Tebangan', 'url' => route('hasil-tebang.index')],
    ['title' => 'Edit Data']
];
@endphp

@extends('layouts.master')

@section('page-title', $header)

@push('styles')
<style>
    .edit-container {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.25rem;
        color: #1f2937;
    }

    .form-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background-color: #f9fafb;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        border-color: #2563eb;
        outline: none;
        background-color: #ffffff;
    }

    .btn-primary {
        background-color: #2563eb;
        color: #ffffff;
        padding: 0.5rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        transition: background-color 0.2s;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        padding: 0.5rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-xl font-bold mb-4">Edit Data Hasil Tebang - {{ $hasil->kode_hasil_tebang }}</h2>

    {{-- Notifikasi sukses atau error --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('hasil-tebang.update', $hasil->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Informasi Umum -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-1">Informasi Umum</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="font-medium">Kode Hasil Tebang</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_hasil_tebang }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">Tanggal Timbang</label>
                        <input type="date" class="form-input bg-gray-100" value="{{ $hasil->tanggal_timbang }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">No LKT</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_lkt }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Data LKT -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-1">Data LKT</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="font-medium">No SPT</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_spt }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">Kode Petak</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_petak }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">Vendor Tebang</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->vendorTebang->nama_vendor ?? $hasil->vendor_tebang }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">Vendor Angkut</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->vendorAngkut->nama_vendor ?? $hasil->vendor_angkut }}" readonly>
                    </div>
                    <div>
                        <label class="font-medium">Supir</label>
                        <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_lambung }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Data Timbangan -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-1">Data Timbangan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bruto" class="font-medium">Total Bruto (ton)</label>
                        <input type="number" name="bruto" id="bruto" step="0.01" class="form-input" value="{{ number_format($hasil->bruto, 2, '.', '') }}" required>
                    </div>
                    <div>
                        <label for="tanggal_bruto" class="font-medium">Tanggal & Jam Bruto</label>
                        <input type="datetime-local" name="tanggal_bruto" id="tanggal_bruto" class="form-input"
                            value="{{ \Carbon\Carbon::parse($hasil->tanggal_bruto)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div>
                        <label for="tarra" class="font-medium">Total Tarra (ton)</label>
                        <input type="number" name="tarra" id="tarra" step="0.01" class="form-input" value="{{ number_format($hasil->tarra, 2, '.', '') }}" required>
                    </div>
                    <div>
                        <label for="tanggal_tarra" class="font-medium">Tanggal & Jam Tarra</label>
                        <input type="datetime-local" name="tanggal_tarra" id="tanggal_tarra" class="form-input"
                            value="{{ \Carbon\Carbon::parse($hasil->tanggal_tarra)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div>
                        <label for="netto1" class="font-medium">Netto 1 (ton)</label>
                        <input type="number" name="netto1" id="netto1" step="0.01" class="form-input bg-gray-100" value="{{ number_format($hasil->netto1, 2, '.', '') }}" readonly>
                    </div>
                    <div>
                        <label for="sortase" class="font-medium">Sortase (ton)</label>
                        <input type="number" name="sortase" id="sortase" step="0.01" class="form-input" value="{{ number_format($hasil->sortase, 2, '.', '') }}" required>
                    </div>
                    <div>
                        <label for="netto2" class="font-medium">Netto 2 (ton)</label>
                        <input type="number" name="netto2" id="netto2" step="0.01" class="form-input bg-gray-100" value="{{ number_format($hasil->netto2, 2, '.', '') }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('hasil-tebang.edit.selection') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    ← Kembali ke Pemilihan Data
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function calculateNetto() {
            const bruto = parseFloat(document.getElementById('bruto').value) || 0;
            const tarra = parseFloat(document.getElementById('tarra').value) || 0;
            const sortase = parseFloat(document.getElementById('sortase').value) || 0;

            const netto1 = bruto - tarra;
            const netto2 = netto1 - sortase;

            document.getElementById('netto1').value = netto1.toFixed(2);
            document.getElementById('netto2').value = netto2.toFixed(2);
        }

        ['bruto', 'tarra', 'sortase'].forEach(id => {
            document.getElementById(id).addEventListener('input', calculateNetto);
        });

        calculateNetto();
    });

    // Auto hide notifikasi setelah 3 detik
    setTimeout(() => {
        document.querySelectorAll('.bg-green-100, .bg-red-100').forEach(el => el.style.display = 'none');
    }, 3000);
</script>
@endpush