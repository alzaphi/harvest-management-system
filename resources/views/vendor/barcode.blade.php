@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Barcode Vendor</h1>
            <p class="text-gray-600">{{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</p>
        </div>
        
        <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg mb-6">
            <!-- Tampilkan QR Code -->
            <div class="mb-4">
                {!! $qrCode !!}
            </div>
            
            <!-- Kode Vendor -->
            <div class="text-center">
                <p class="text-sm text-gray-500 mb-2">Scan kode di atas untuk mengakses halaman LKT</p>
                <p class="font-mono bg-gray-100 px-3 py-1 rounded text-sm">{{ $vendor->kode_vendor }}</p>
            </div>
        </div>
        
        <!-- Tombol Unduh -->
        <div class="flex justify-center space-x-4">
            <a href="{{ route('barcode.download', $vendor->id) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-download mr-2"></i>Unduh Barcode
            </a>
            <a href="{{ route('vendor.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>
</div>
@endsection
