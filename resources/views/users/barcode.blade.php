@extends('layouts.master')

@if(session('error'))
    <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

@section('content')
<div class="container mx-auto px-2 py-2">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-2">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Barcode Vendor</h1>
            <p class="text-gray-600">{{ $user->vendor->nama_vendor ?? $user->name }}</p>
            @if(isset($user->vendor->all_kode_vendor) && $user->vendor->all_kode_vendor !== 'N/A')
                <p class="text-sm text-gray-700 mt-1">
                    Kode Vendor: {{ $user->vendor->all_kode_vendor }}
                </p>
            @else
                <p class="text-sm text-gray-700 mt-1">
                    Kode Vendor: {{ $user->vendor->kode_vendor ?? 'N/A' }}
                </p>
            @endif
            
            @if($user->vendor)
            <div class="mt-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Akun Vendor Aktif
                </span>
            </div>
            @endif
        </div>
        
        <div class="bg-blue-50 border-l-4 border-blue-400 p-2 mb-4 rounded">
            <div class="flex items-start">
                <div class="flex-shrink-0 pt-0.5">
                    <svg class="h-4 w-4 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h2a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-2">
                    <p class="text-xs text-blue-700 m-0">
                        Barcode ini digunakan untuk menghitung hasil tebang pada LKT. <br>
                        <strong>Simpan dan cetak barcode ini</strong>.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col items-center justify-between p-3 border-2 border-dashed border-gray-300 rounded-lg mb-4 bg-white" style="height: 389px; overflow: hidden;">
            <!-- Spacer untuk menempatkan nama di tengah -->
            <div class="flex-1 flex items-center justify-center w-full">
                <h3 class="text-lg font-medium text-gray-900 text-center">{{ $user->vendor->nama_vendor ?? $user->name }}</h3>
            </div>
            
            <!-- QR Code -->
            <div class="p-2 bg-white rounded-lg flex-1 flex items-center justify-center">
                @if(strpos($qrCode, 'data:image/svg+xml;base64,') === 0)
                    <img src="{{ $qrCode }}" alt="QR Code" class="mx-auto" style="width: 180px; height: 180px;">
                @else
                    {!! str_replace('height="300"', 'height="180" width="180"', $qrCode) !!}
                @endif
            </div>
            
            <!-- Vendor Info -->
            <div class="text-center mt-2">
                <p class="text-xs text-gray-500 mb-1">Kode Unik</p>
                <p class="font-mono bg-gray-100 px-3 py-1.5 rounded-lg text-xs font-medium mb-2">
                    {{ substr($url, strrpos($url, '/') + 1) }}
                </p>
                
                <p class="text-xs text-gray-500">Scan kode QR untuk verifikasi vendor</p>
                <p class="text-xs text-gray-400 mt-0.5">Valid untuk {{ $user->vendor->nama_vendor ?? $user->name }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center gap-2 mt-3 mx-auto w-full max-w-md">
            <a href="{{ route('barcode.download', $user->id) }}" 
               class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 no-underline whitespace-nowrap">
                <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Unduh Barcode
            </a>
            
            <a href="{{ route('users.index') }}" 
               class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 border border-gray-300 no-underline whitespace-nowrap">
                <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Kembali ke Daftar User
            </a>
        </div>
    </div>
</div>


@endsection
