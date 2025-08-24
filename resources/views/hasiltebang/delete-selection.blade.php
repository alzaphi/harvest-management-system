@extends('layouts.master')

@section('page-title', 'Pilih Data Hasil Tebang untuk Dihapus')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-xl font-bold text-red-700 mb-4">Pilih Data Hasil Tebang untuk Dihapus</h2>

    <div class="bg-white rounded-lg shadow p-6">
        <form id="deleteSelectionForm" class="space-y-4">
            @csrf
            <div class="mb-4">
                <label for="kode_hasil_tebang" class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih Data Hasil Tebang
                </label>
                <select id="kode_hasil_tebang" name="id" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Data Hasil Tebang --</option>
                    @foreach($allHasilTebang as $hasil)
                        @php
                            $vendorTebang = \App\Models\Vendor::where('kode_vendor', $hasil->vendor_tebang)->first();
                            $vendorAngkut = \App\Models\Vendor::where('kode_vendor', $hasil->vendor_angkut)->first();
                        @endphp
                        <option value="{{ $hasil->id }}">
                            {{ $hasil->kode_hasil_tebang }} | Tebang: {{ $vendorTebang->nama_vendor ?? $hasil->vendor_tebang }} | Angkut: {{ $vendorAngkut->nama_vendor ?? $hasil->vendor_angkut }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('hasil-tebang.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Kembali
                </a>
                <button type="button" 
                        onclick="submitDeleteForm()"
                        class="btn-delete">
                    Lanjutkan ke Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .btn-delete {
        background-color: #dc2626; /* merah */
        color: white;
        font-weight: bold;
        padding: 10px 20px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .btn-delete:hover {
        background-color: #b91c1c; /* merah gelap */
        transform: translateY(-1px);
    }
</style>

<script>
function submitDeleteForm() {
    const select = document.getElementById('kode_hasil_tebang');
    const id = select.value;
    
    if (id) {
        window.location.href = "{{ route('hasil-tebang.delete.confirm', '') }}/" + id;
    } else {
        alert('Silakan pilih data hasil tebang terlebih dahulu');
    }
}
</script>
@endsection
