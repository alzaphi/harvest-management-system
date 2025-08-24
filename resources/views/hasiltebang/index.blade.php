@extends('layouts.master')

@php
$header = 'Hasil Tebangan';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Hasil Tebangan', 'url' => route('hasil-tebang.index')]
];
@endphp

@section('page-title', $header)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hasil-tebang.css') }}">
@endpush

@section('content')
<section class="p-6">
    <h2 class="text-[#1e40af] font-bold text-lg mb-6">Hasil Tebangan</h2>

    <!-- Search -->
    <form method="GET" action="{{ route('hasil-tebang.index') }}" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <input
                type="text"
                name="search"
                placeholder="Cari vendor atau kode..."
                value="{{ request('search') }}"
                class="w-full max-w-sm border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"
            />
            <input type="hidden" name="jenis" value="{{ $jenis }}" />
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                🔍 Cari
            </button>
        </div>
    </form>

    <!-- Tabs -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-6">
            <a href="{{ route('hasil-tebang.index', ['jenis' => 'tebang']) }}"
               class="px-5 py-2 rounded border-2 font-semibold
                      {{ $jenis === 'tebang' ? 'border-blue-700 bg-blue-100 text-blue-900' : 'border-transparent text-gray-700 hover:border-blue-700 hover:bg-blue-100 hover:text-blue-900' }}">
                Vendor Tebang
            </a>
            <a href="{{ route('hasil-tebang.index', ['jenis' => 'angkut']) }}"
               class="px-5 py-2 rounded border-2 font-semibold
                      {{ $jenis === 'angkut' ? 'border-blue-700 bg-blue-100 text-blue-900' : 'border-transparent text-gray-700 hover:border-blue-700 hover:bg-blue-100 hover:text-blue-900' }}">
                Vendor Angkut
            </a>
        </div>
        @if(isset($vendors) && count($vendors) > 0)
        <div class="flex space-x-2">
            <a href="{{ route('hasil-tebang.create') }}" class="btn-tambah bg-blue-600 hover:bg-blue-700 no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Data Hasil Tebang
            </a>
            <a href="{{ route('hasil-tebang.edit.selection') }}" class="btn-tambah bg-yellow-500 hover:bg-yellow-600 no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Data
            </a>
            <a href="{{ route('hasil-tebang.delete.selection') }}"
               class="btn-tambah bg-red-500 hover:bg-red-600 no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Data
            </a>
        </div>
        @endif
    </div>

    <!-- Vendor Selection Modal -->
    <div id="vendorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Pilih Vendor</h3>
                <div class="mt-2 px-7 py-3">
                    <select id="vendorSelect" class="w-full border rounded p-2">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->kode_vendor }}">{{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="ok-btn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Pilih
                    </button>
                    <button onclick="hideVendorSelection()" class="ml-2 px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Grid -->
    <div class="card-grid">
        @forelse($vendors as $vendor)
            <div class="vendor-card">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Vendor {{ ucfirst($jenis) }}</p>
                        <h3 title="{{ $vendor->nama_vendor }}">{{ $vendor->nama_vendor }}</h3>
                        <p class="text-xs font-semibold text-gray-600">Kode: {{ $vendor->kode_vendor }}</p>
                    </div>
                    @php
                        // Check if there's any data for this vendor
                        $hasData = $jenis === 'angkut' 
                            ? \App\Models\HasilTebang::where('vendor_angkut', $vendor->kode_vendor)->exists()
                            : \App\Models\HasilTebang::where('vendor_tebang', $vendor->kode_vendor)->exists();
                    @endphp
                    
                    @if($hasData)
                        @if($jenis === 'angkut')
                            <a href="{{ route('hasil-tebang.show', $vendor->kode_vendor) }}?jenis=angkut" class="btn-detail no-underline">
                                Lihat Detail
                            </a>
                        @else
                            <a href="{{ route('hasil-tebang.show', $vendor->kode_vendor) }}?jenis=tebang" class="btn-detail no-underline">
                                Lihat Detail
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-full">Belum ada data vendor {{ $jenis }}.</p>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
    function showVendorSelection() {
        document.getElementById('vendorModal').classList.remove('hidden');
    }

    function hideVendorSelection() {
        document.getElementById('vendorModal').classList.add('hidden');
    }

    document.getElementById('ok-btn').addEventListener('click', function() {
        const selectedVendor = document.getElementById('vendorSelect').value;
        if (selectedVendor) {
            window.location.href = "{{ route('hasil-tebang.create', '') }}/" + selectedVendor;
        }
    });
</script>
@endpush
