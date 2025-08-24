@extends('layouts.master')

@section('title', 'Dashboard')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="px-0 pt-0 pb-6">
    <div class="vendor-container">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Selamat Datang di Harvest Management System!</h2>

        <!-- Summary Cards -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan</h3>
            <div class="dashboard-container flex flex-wrap justify-center">
                <!-- Total Mandor Aktif -->
                @if(auth()->user()->role_name != 'GIS Division' && auth()->user()->role_name != 'PT PAG' && auth()->user()->role_name != 'QA')
                <a href="{{ route('foreman.index') }}" class="dashboard-card card-blue hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="card-title">Total Mandor Aktif</div>
                    <div class="card-value">{{ $totalMandor ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'GIS Division' && auth()->user()->role_name != 'vendor' && auth()->user()->role_name != 'PT PAG' && auth()->user()->role_name != 'QA')
                <!-- Total Vendor Aktif -->
                <a href="{{ route('vendor.index') }}" class="dashboard-card card-green hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="card-title">Total Vendor Aktif</div>
                    <div class="card-value">{{ $totalVendor ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'GIS Division' && auth()->user()->role_name != 'vendor' && auth()->user()->role_name != 'PT PAG' && auth()->user()->role_name != 'QA')
                <!-- Total Kendaraan Vendor -->
                <a href="{{ route('vehicles.index') }}" class="dashboard-card card-pink hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div class="card-title">Total Kendaraan Vendor</div>
                    <div class="card-value">{{ $totalKendaraanVendor ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'PT PAG')
                <!-- Sub Block Belum Ditebang -->
                <a href="{{ route('harvest-sub-blocks.index', ['status' => 'planned']) }}" class="dashboard-card card-yellow hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="card-title">Sub Block Belum Ditebang</div>
                    <div class="card-value">{{ $belumDitebang ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'PT PAG')
                <!-- Sub Block Sedang Ditebang -->
                <a href="{{ route('harvest-sub-blocks.index', ['status' => 'in_progress']) }}" class="dashboard-card card-orange hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="card-title">Sub Block Sedang Ditebang</div>
                    <div class="card-value">{{ $sedangDitebang ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'PT PAG')
                <!-- Sub Block Sudah Ditebang -->
                <a href="{{ route('harvest-sub-blocks.index', ['status' => 'completed']) }}" class="dashboard-card card-purple hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="card-title">Sub Block Sudah Ditebang</div>
                    <div class="card-value">{{ $sudahDitebang ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'GIS Division' && auth()->user()->role_name != 'PT PAG' && auth()->user()->role_name != 'QA')
                <!-- SPT Card - Different for Assistant Manager Plantation -->
                @if(auth()->user()->role_name === 'Assistant Manager Plantation')
                <a href="{{ route('spt.index') }}" class="dashboard-card card-indigo hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div class="card-title">Total SPT DIPROSES</div>
                    <div class="card-value">{{ $totalSpt ?? 0 }}</div>
                </a> 
                @else
                <a href="{{ route('spt.index') }}" class="dashboard-card card-indigo hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div class="card-title">Total SPT DIPROSES</div>
                    <div class="card-value">{{ $totalSpt ?? 0 }}</div>
                </a>
                @endif
                @endif

                @if(auth()->user()->role_name !== 'GIS Division' && auth()->user()->role_name !== 'PT PAG' && auth()->user()->role_name !== 'QA')
                    @if(auth()->user()->role_name === 'Assistant Manager Plantation')
                    <!-- LKT Card for Assistant Manager Plantation -->
                    <a href="{{ route('lkt.index') }}" class="dashboard-card card-red hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="card-title">TOTAL LKT DIPROSES</div>
                        <div class="card-value">{{ $lktDiproses ?? 0 }}</div>
                    </a>
                    @else
                    <!-- LKT Card for other roles (except PT PAG and GIS) -->
                    <a href="{{ route('lkt.index') }}" class="dashboard-card card-red hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                        <div class="card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="card-title">Total LKT DIPROSES</div>
                        <div class="card-value">{{ $lktDiproses ?? 0 }}</div>
                    </a>
                    @endif
                @endif

                <!-- LKT Card for PT PAG -->
                @if(auth()->user()->role_name === 'PT PAG')
                <a href="{{ route('lkt.index', ['status' => 'Disetujui,Selesai']) }}" class="dashboard-card card-green hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="card-title">LKT YANG MENUNGGU PERSETUJUAN</div>
                    <div class="card-value">{{ $lktDiproses ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'PT PAG' && auth()->user()->role_name != 'Assistant Divisi Plantation' && auth()->user()->role_name != 'GIS Division')
                <!-- BAPP -->
                <a href="{{ route('bapp.index')}}" class="dashboard-card card-cyan hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="card-title">BAPP MENUNGGU DIPROSES</div>
                    <div class="card-value">{{ $totalBapp ?? 0 }}</div>
                </a>
                @endif

                @if(auth()->user()->role_name != 'PT PAG')
                <!-- Hasil Tebangan vs Target -->
                <div class="dashboard-card card-teal w-full md:w-1/5 xl:w-1/5 p-4">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="card-title">Hasil Tebangan vs Target</div>
                    <div class="mt-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Sudah Ditebang</span>
                            <span>{{ number_format($luasAreaTebangan['sudah_ditebang'] ?? 0, 2) }} ha ({{ $luasAreaTebangan['persen_sudah'] ?? 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                            <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $luasAreaTebangan['persen_sudah'] ?? 0 }}%"></div>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Belum Ditebang</span>
                            <span>{{ number_format($luasAreaTebangan['belum_ditebang'] ?? 0, 2) }} ha ({{ $luasAreaTebangan['persen_belum'] ?? 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: {{ $luasAreaTebangan['persen_belum'] ?? 0 }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-center text-gray-300">
                            Total: {{ number_format($luasAreaTebangan['total_luas'] ?? 0, 2) }} ha
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
