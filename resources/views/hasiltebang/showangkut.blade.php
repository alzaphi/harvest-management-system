@extends('layouts.master')

@section('page-title', 'Detail Hasil Tebangan - ' . $vendor->nama_vendor)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')
<div class="vendor-container">
    <h2>Detail Hasil Tebangan - {{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</h2>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded relative alert-message">
            {{ session('success') }}
            <button type="button" class="absolute top-0 right-0 px-3 py-2 text-green-800 hover:text-green-900 close-alert">
                &times;
            </button>
        </div>
    @endif

    <!-- Filter & Actions -->
    <div class="vendor-header mb-12">
        <form action="#" method="GET" class="search-form w-full flex flex-wrap md:flex-nowrap gap-3 items-center justify-between" id="search-form">
            <input type="text" name="search" id="search-input" placeholder="Cari kode LKT atau SPT..." class="search-input flex-1 min-w-[200px]">
            <input type="date" name="tanggal" class="search-input flex-1 min-w-[200px]">
        </form>

        <div class="btn-group">
           <a href="{{ route('bapp.angkut.generate', $vendor->kode_vendor) }}" class="bg-blue-700 text-white font-semibold rounded px-4 py-2 text-sm hover:bg-blue-800 transition">
                Generate BAPP Angkut
            </a>
            <a href="#" class="btn btn-excel">Download Excel</a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="vendor-table text-xs text-center">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Hasil Tebang</th>
                    <th>Tanggal Timbang</th>
                    <th>Petak & Luas</th>
                    <th>Divisi</th>
                    <th>Jenis Tebang</th>
                    <th>Supir</th>
                    <th>Vendor Tebang</th>
                    <th>Zonasi</th>
                    <th>Total Tebangan (Ton)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hasilTebangs as $index => $hasil)
                    @php
                        $subBlock = \App\Models\SubBlock::where('kode_petak', $hasil->kode_petak)->first();
                        $vehicle = \App\Models\Vehicle::where('kode_lambung', $hasil->kode_lambung)->first();
                        $vendorTebang = \App\Models\VendorAngkut::where('kode_vendor', $hasil->vendor_tebang)
                            ->where('jenis_vendor', 'Vendor Tebang')
                            ->first();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $hasil->kode_hasil_tebang }}</td>
                        <td>{{ \Carbon\Carbon::parse($hasil->tanggal_timbang)->format('d/m/Y') }}</td>
                        <td class="text-left">
                            {{ $hasil->kode_petak }}
                            @if($subBlock && $subBlock->luas_area)
                                <br><span class="text-xs text-gray-500">Luas: {{ number_format($subBlock->luas_area, 2) }} ha</span>
                            @endif
                        </td>
                        <td>{{ $subBlock->divisi ?? '-' }}</td>
                        <td>{{ $hasil->jenis_tebang }}</td>
                        <td class="text-left">
                            @if($vehicle)
                                {{ $vehicle->nama_vendor ?? '-' }}
                                <br><span class="text-xs text-gray-500">Lambung: {{ $vehicle->kode_lambung }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($vendorTebang)
                                {{ $vendorTebang->nama_vendor ?? $hasil->vendor_tebang }}
                                <br><span class="text-xs text-gray-500">{{ $hasil->vendor_tebang }}</span>
                            @else
                                {{ $hasil->vendor_tebang }}
                            @endif
                        </td>
                        <td>{{ $hasil->zonasi }}</td>
                        <td class="font-semibold">
                            {{ number_format($hasil->netto2, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($hasil->status_angkut === 'Generated')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Generated
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    Not Generated
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-gray-500 py-4">Belum ada data hasil tebangan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <a href="{{ route('hasil-tebang.index', ['jenis' => 'angkut']) }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
            Kembali
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-close alert
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            const alert = document.querySelector('.alert-message');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 5000);

        // Close alert when close button is clicked
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.alert-message').style.display = 'none';
            });
        });
    });
</script>
@endpush
