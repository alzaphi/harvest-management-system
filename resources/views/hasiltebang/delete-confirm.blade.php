@extends('layouts.master')

@section('page-title', 'Konfirmasi Hapus Data Hasil Tebang')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <h2 class="text-xl font-bold text-red-600 mb-6">Konfirmasi Hapus</h2>

        <p class="mb-6 text-gray-700">
            Apakah Anda yakin ingin menghapus data hasil tebang berikut? Data ini tidak dapat dikembalikan setelah dihapus.
        </p>

        <div class="grid grid-cols-2 gap-x-8 gap-y-3 bg-gray-50 p-5 rounded-lg mb-6 text-sm">
            <p><strong>Kode Hasil Tebang:</strong> {{ $hasil->kode_hasil_tebang }}</p>
            <p><strong>Tanggal Timbang:</strong> {{ $hasil->tanggal_timbang }}</p>

            <p><strong>No LKT:</strong> {{ $hasil->kode_lkt }}</p>
            <p><strong>No SPT:</strong> {{ $hasil->kode_spt }}</p>

            <p><strong>Kode Petak:</strong> {{ $hasil->kode_petak }}</p>
            <p><strong>Divisi:</strong> {{ $hasil->divisi }}</p>

            <p><strong>Zonasi:</strong> {{ $hasil->zonasi }}</p>
            <p><strong>Jenis Tebang:</strong> {{ $hasil->jenis_tebang }}</p>

            <p><strong>Vendor Tebang:</strong> {{ $vendorTebang->nama_vendor ?? $hasil->vendor_tebang }}</p>
            <p><strong>Vendor Angkut:</strong> {{ $vendorAngkut->nama_vendor ?? $hasil->vendor_angkut }}</p>

            <p><strong>Kendaraan:</strong> {{ $hasil->kode_lambung }}</p>
            <p><strong>Bruto:</strong> {{ $hasil->bruto }}</p>

            <p><strong>Tarra:</strong> {{ $hasil->tarra }}</p>
            <p><strong>Netto 1:</strong> {{ $hasil->netto1 }}</p>

            <p><strong>Sortase:</strong> {{ $hasil->sortase }}</p>
            <p><strong>Netto 2:</strong> {{ $hasil->netto2 }}</p>
        </div>

        <form action="{{ route('hasil-tebang.destroy', $hasil->id) }}" method="POST" class="flex justify-between">
            @csrf
            @method('DELETE')

            <a href="{{ route('hasil-tebang.delete.selection') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                Batal
            </a>
            <button type="submit" 
                    class="btn-delete">
                Hapus Data
            </button>
        </form>
    </div>
</div>

<style>
    .btn-delete {
        background-color: #dc2626;
        color: white;
        font-weight: bold;
        padding: 10px 20px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .btn-delete:hover {
        background-color: #b91c1c;
        transform: translateY(-1px);
    }
</style>
@endsection
