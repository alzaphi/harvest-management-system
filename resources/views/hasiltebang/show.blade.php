@extends('layouts.master')

@php
$header = 'Detail Hasil Tebangan - ' . $vendor->nama_vendor;
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Hasil Tebangan', 'url' => route('hasil-tebang.index')],
    ['title' => 'Detail - ' . $vendor->nama_vendor]
];
@endphp

@section('page-title', $header)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')
<div class="vendor-container">
    <h2>Detail Hasil Tebangan - {{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</h2>

    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded relative alert-message">
            {{ session('success') }}
            <button type="button" class="absolute top-0 right-0 px-3 py-2 text-green-800 hover:text-green-900 close-alert">
                &times;
            </button>
        </div>
    @endif

    <div class="vendor-header mb-6" style="margin-bottom: 1.5rem !important;">
        <form action="#" method="GET" class="search-form w-full flex flex-wrap md:flex-nowrap gap-3 items-center justify-between" id="search-form">
            <input type="text" name="search" id="search-input" placeholder="Cari kode LKT atau SPT..." class="search-input flex-1 min-w-[200px]">
            <input type="date" name="tanggal" class="search-input flex-1 min-w-[200px]">
        </form>

        <div class="btn-group">
        
        @can('generate-bapp')
            <a href="{{ route('bapp.generate-selection', ['kode_vendor' => $vendor->kode_vendor]) }}" class="bg-blue-700 text-white font-semibold rounded px-4 py-2 text-sm hover:bg-blue-800 transition no-underline">
                Generate BAPP Tebang
            </a>
            @endcan
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
                    <th>Vendor Angkut</th>
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
                        $vendorAngkut = \App\Models\VendorAngkut::where('kode_vendor', $hasil->vendor_angkut)
                            ->where('jenis_vendor', 'Vendor Angkut')
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
                                {{ $hasil->driver ?? '-' }}
                            @endif
                        </td>
                        <td>
                            @if($vendorAngkut)
                                {{ $vendorAngkut->nama_vendor ?? $hasil->vendor_angkut }}
                                <br><span class="text-xs text-gray-500">{{ $hasil->vendor_angkut }}</span>
                            @else
                                {{ $hasil->vendor_angkut ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $hasil->zonasi ?? '-' }}</td>
                        <td class="font-semibold">
                            {{ number_format($hasil->netto2, 2) }}
                        </td>
                        <td>
                            @php
                                // Convert status to lowercase for consistent comparison
                                $status = strtolower($hasil->status ?? 'not_generated');
                                $statusLabels = [
                                    'generated' => 'Generated',
                                    'on_process' => 'On Process',
                                    'cancelled' => 'Cancelled',
                                    'not generated' => 'Not Generated',
                                    'not_generated' => 'Not Generated'  // Keep both for backward compatibility
                                ];
                                $statusClasses = [
                                    'generated' => 'bg-green-100 text-green-800',
                                    'on_process' => 'bg-yellow-100 text-yellow-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'not generated' => 'bg-red-100 text-red-800',
                                    'not_generated' => 'bg-red-100 text-red-800'  // Keep both for backward compatibility
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$status] ?? 'bg-red-100 text-red-800' }}">
                                {{ $statusLabels[$status] ?? 'Not Generated' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">Tidak ada data hasil tebangan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $hasilTebangs->links() }}
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('hasil-tebang.index') }}" class="inline-block px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition no-underline">
            Kembali
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Close alert message
    document.addEventListener('DOMContentLoaded', function() {
        const closeButtons = document.querySelectorAll('.close-alert');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.alert-message').style.display = 'none';
            });
        });
    });
</script>
@endpush
