@extends('layouts.master')
@php
    $header = 'Daftar BAPP';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Daftar BAPP', 'url' => route('bapp.index')],
    ];
@endphp

@section('content')

@push('styles')
<style>
    body { background-color: #f9fafb; }
    
    .card {
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    table { min-width: 100%; background: white; }
    
    th {
        background-color: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    th, td { padding: 0.75rem 1.5rem; white-space: nowrap; }
    tr:not(:last-child) { border-bottom: 1px solid #e5e7eb; }
    tr:hover { background-color: #f9fafb; }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-generated { background-color: #dcfce7; color: #166534; }
    .status-pending { background-color: #fef3c7; color: #92400e; }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-view { color: #3b82f6; background-color: #eff6ff; }
    .btn-view:hover { background-color: #dbeafe; }
    .btn-edit { color: #f59e0b; background-color: #fffbeb; }
    .btn-edit:hover { background-color: #fef3c7; }
    .btn-delete { color: #ef4444; background-color: #fef2f2; }
    .btn-delete:hover { background-color: #fee2e2; }
    
    .search-input {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.5rem 1rem;
        width: 100%;
        max-width: 300px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .tab {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;
    }
    
    .tab:hover { background-color: #f3f4f6; color: #374151; }
    .tab-active { color: #1f2937; border-bottom: 3px solid #3b82f6; }
    
    @media (max-width: 768px) {
        th, td { padding: 0.5rem 0.75rem; }
        .action-btn { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <div class="flex">
                <div class="py-1">
                    <svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Sukses!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <div class="flex">
                <div class="py-1">
                    <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Error!</p>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar BAPP</h1>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ route('bapp.index', ['jenis' => 'tebang']) }}" class="tab {{ request('jenis', 'tebang') === 'tebang' ? 'tab-active' : '' }}">
            BAPP Tebang
        </a>
        <a href="{{ route('bapp.index', ['jenis' => 'angkut']) }}" class="tab {{ request('jenis') === 'angkut' ? 'tab-active' : '' }}">
            BAPP Angkut
        </a>
    </div>

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('bapp.index') }}" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <input
                type="text"
                name="search"
                placeholder="Cari kode BAPP atau vendor..."
                value="{{ request('search') }}"
                class="w-full max-w-sm border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"
            />
            <input type="hidden" name="jenis" value="{{ request('jenis', 'tebang') }}" />
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                🔍 Cari
            </button>
        </div>
    </form>

    <!-- BAPP Table -->
    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Kode BAPP</th>
                        <th class="text-left">Tanggal BAPP</th>
                        <th class="text-left">Periode BAPP</th>
                        <th class="text-left">Nama Vendor</th>
                        <th class="text-left">Deskripsi Komplain</th>
                        <th class="text-center">Tgl. Komplain</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bapps as $index => $bapp)
                        <tr class="hover:bg-gray-50">
                            <td class="text-gray-600">{{ ($bapps->currentPage() - 1) * $bapps->perPage() + $loop->iteration }}</td>
                            <td class="font-medium text-gray-900">{{ $bapp->kode_bapp }}</td>
                            <td class="text-gray-600">{{ \Carbon\Carbon::parse($bapp->tanggal_bapp)->format('d/m/Y') }}</td>
                            <td class="text-gray-600">{{ $bapp->periode_bapp ?? '-' }}</td>
                            <td class="text-gray-700">
                                @php
                                    $vendorName = 'Vendor tidak ditemukan';

                                    // Check if vendor relationship is loaded
                                    if ($bapp->vendor) {
                                        $vendorName = $bapp->vendor->nama_vendor;
                                    }
                                    // Fallback: Try to get vendor manually based on BAPP type
                                    else {
                                        $vendorCode = $bapp->vendor_tebang ?? $bapp->vendor_angkut;
                                        if ($vendorCode) {
                                            $vendorModel = request('jenis', 'tebang') === 'tebang'
                                                ? '\\App\\Models\\VendorTebang'
                                                : '\\App\\Models\\VendorAngkut';
                                            $vendor = $vendorModel::where('kode_vendor', $vendorCode)
                                                ->first(['nama_vendor']);
                                            $vendorName = $vendor ? $vendor->nama_vendor : 'Vendor tidak ditemukan';
                                        }
                                    }
                                    echo $vendorName;
                                @endphp
                            </td>
                            <td class="text-sm text-gray-700 max-w-xs">
                                @if(!empty($bapp->deskripsi_komplain))
                                    @php
                                        // Split the descriptions by the delimiter
                                        $descriptions = explode('|||', $bapp->deskripsi_komplain);
                                    @endphp
                                    <div class="space-y-1">
                                        @foreach($descriptions as $desc)
                                            @if(trim($desc))
                                                <div class="flex items-start">
                                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mt-2 mr-2 flex-shrink-0"></span>
                                                    <span class="text-sm">{{ $desc }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="text-center text-sm text-gray-600">
                                @if(!empty($bapp->tanggal_terakhir_komplain))
                                    {{ \Carbon\Carbon::parse($bapp->tanggal_terakhir_komplain)->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'Diajukan' => 'bg-yellow-100 text-yellow-800',
                                        'pending_vendor' => 'bg-blue-100 text-blue-800',
                                        'pending_approval' => 'bg-purple-100 text-purple-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'generated' => 'bg-green-100 text-green-800'
                                    ][$bapp->status] ?? 'bg-gray-100 text-gray-800';

                                    $statusText = [
                                        'draft' => 'Draft',
                                        'Diajukan' => 'Diajukan',
                                        'pending_vendor' => 'Menunggu Vendor',
                                        'pending_approval' => 'Menunggu Approval',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        'generated' => 'Generated'
                                    ][$bapp->status] ?? $bapp->status;

                                    $statusIcon = [
                                        'draft' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'Diajukan' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                        'pending_vendor' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'pending_approval' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'approved' => 'M5 13l4 4L19 7',
                                        'rejected' => 'M6 18L18 6M6 6l12 12',
                                        'generated' => 'M5 13l4 4L19 7'
                                    ][$bapp->status] ?? 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}" />
                                    </svg>
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    // Determine the type based on the current tab or request parameter
                                    $jenis = request('jenis', 'tebang');

                                    // If we have a vendor, we can be more specific
                                    if (isset($bapp->vendor_angkut)) {
                                        $jenis = 'angkut';
                                    } elseif (isset($bapp->vendor_tebang)) {
                                        $jenis = 'tebang';
                                    }
                                @endphp
                                <div class="flex items-center justify-center space-x-2 flex-wrap">
                                    <a href="{{ route('bapp.show', ['jenis' => $jenis, 'bapp' => $bapp->id]) }}" class="action-btn btn-view no-underline">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </a>
                                    
                                    @if(auth()->user()->hasRole('manager_plantation') && in_array($bapp->status, ['Diajukan', 'pending_approval']))
                                        <form action="{{ route('bapp.approve', ['jenis' => $jenis, 'bapp' => $bapp->id]) }}" method="POST" class="inline-block mb-1" onsubmit="return confirm('Approve BAPP ini sebagai Manager Plantation?')">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="approval_type" value="plantation">
                                            <button type="submit" class="bg-green-500 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-green-600 flex items-center transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Approve Plantation
                                            </button>
                                        </form>
                                    @endif

                                    @if(auth()->user()->hasRole('manager_cdr') && $bapp->status === 'pending_cdr_approval')
                                        <form action="{{ route('bapp.approve', ['jenis' => $jenis, 'bapp' => $bapp->id]) }}" method="POST" class="inline-block mb-1" onsubmit="return confirm('Approve BAPP ini sebagai Manager CDR?')">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="approval_type" value="cdr">
                                            <button type="submit" class="bg-blue-500 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-blue-600 flex items-center transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Approve CDR
                                            </button>
                                        </form>
                                    @endif
                                @can('edit-bapp')
                                    @if($jenis === 'angkut')
                                        <a href="{{ route('bapp.angkut.edit', $bapp->kode_bapp) }}" class="action-btn btn-edit no-underline">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @else
                                        <a href="{{ route('bapp.edit', ['jenis' => $jenis, 'bapp' => $bapp->id]) }}" class="action-btn btn-edit no-underline">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endif
                                    <form action="{{ route('bapp.destroy', ['jenis' => $jenis, 'bapp' => $bapp->id]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus BAPP ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn btn-delete">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data BAPP ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($bapps->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $bapps->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Add any necessary JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchValue = this.value.trim();
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', searchValue);
                    window.location.href = url.toString();
                }
            });
        }
    });
</script>
@endpush
@endsection
@endsection
