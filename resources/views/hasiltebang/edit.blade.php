@extends('layouts.master')

@section('page-title', 'Edit Data ')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-xl font-bold mb-4">Edit Data - {{ $hasil->kode_hasil_tebang }}</h2>

    <form action="{{ route('hasil-tebang.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Informasi Umum -->
        <div class="mb-6">
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
        <div class="mb-6">
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
                    <input type="text" class="form-input bg-gray-100" value="{{ $hasil->vendor_tebang }}" readonly>
                </div>
                <div>
                    <label class="font-medium">Vendor Angkut</label>
                    <input type="text" class="form-input bg-gray-100" value="{{ $hasil->vendor_angkut }}" readonly>
                </div>
                <div>
                    <label class="font-medium">Supir</label>
                    <input type="text" class="form-input bg-gray-100" value="{{ $hasil->kode_lambung }}" readonly>
                </div>
            </div>
        </div>

        <!-- Data Timbangan -->
        <div class="mb-6">
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
        <div class="flex justify-between mt-6">
            <a href="{{ route('hasil-tebang.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
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

        // Initialize on page load
        calculateNetto();
    });
</script>
@endpush
