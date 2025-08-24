@extends('layouts.master')

@section('page-title', 'Rekap BAPP')

@php
    $header = 'Rekap BAPP';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Rekap BAPP', 'url' => route('bapp.recap.index')],    ];
@endphp


@push('styles')
<style>
    /* Base Styles */
    body {
        background-color: #f8fafc;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 0.875rem;
        color: #1f2937;
    }
    
    /* Card Styling */
    .card {
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease-in-out;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
    }
    
    /* Filter Section */
    .filter-section {
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        border: 1px solid #e5e7eb;
        padding: 1.25rem !important;
        margin-bottom: 1.5rem;
    }
    
    .filter-section .form-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #4b5563;
        margin-bottom: 0.375rem;
        display: block;
    }
    
    .filter-section .form-select, 
    .filter-section .form-input {
        width: 100%;
        height: 38px;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #fff;
        transition: all 0.2s;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .filter-section .form-select:focus, 
    .filter-section .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }
    
    /* Filter Grid Layout */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        height: 38px;
        border: 1px solid transparent;
    }
    
    .btn-primary {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 1px 2px 0 rgba(16, 24, 40, 0.05);
    }
    
    .btn-primary:hover {
        background-color: #2563eb;
    }
    
    .btn-outline {
        background-color: white;
        border-color: #d1d5db;
        color: #4b5563;
        box-shadow: 0 1px 2px 0 rgba(16, 24, 40, 0.05);
    }
    
    .btn-outline:hover {
        background-color: #f9fafb;
        border-color: #9ca3af;
    }
    
    /* Table Styles */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th {
        background-color: #f9fafb;
        color: #4b5563;
        font-weight: 500;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    
    tr:last-child td {
        border-bottom: none;
    }
    
    tr:hover td {
        background-color: #f9fafb;
    }
    
    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .status-Draft {
        background-color: #f3f4f6;
        color: #374151;
        border-color: #e5e7eb;
    }

    .status-Diajukan {
        background-color: #dbeafe;
        color: #1e40af;
        border-color: #bfdbfe;
    }

    .status-Diverifikasi {
        background-color: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .status-Disetujui {
        background-color: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }

    .status-Ditolak {
        background-color: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .status-Selesai {
        background-color: #e0f2fe;
        color: #075985;
        border-color: #7dd3fc;
    }

    .status-Dibayar {
        background-color: #f3e8ff;
        color: #6b21a8;
        border-color: #d8b4fe;
    }

    /* Vendor Badge */
    .vendor-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 1rem;
        border-top: 1px solid #f3f4f6;
    }
    
    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        margin: 0 0.125rem;
        color: #4b5563;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    
    .pagination .page-link:hover {
        background-color: #f3f4f6;
    }
    
    .pagination .active .page-link {
        background-color: #3b82f6;
        color: white;
    }
    
    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #6b7280;
    }
    
    .empty-state svg {
        margin: 0 auto 1rem;
        color: #9ca3af;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-actions {
            grid-column: 1 / -1;
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Rekap BAPP</h1>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 12l-1.293 1.293a1 1 0 101.414 1.414L10 13.414l2.293 2.293a1 1 0 001.414-1.414L11.414 12l1.293-1.293a1 1 0 00-1.414-1.414L10 10.586 8.707 9.293z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-section p-6 mb-6 bg-white rounded-lg shadow-sm">
        <form action="{{ route('bapp.recap.index') }}" method="GET" class="space-y-4">
            <div class="filter-grid grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Periode Filter -->
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <select id="period" name="period" class="form-select w-full" onchange="this.form.submit()">
                        <option value="">Semua Periode</option>
                        @foreach($allPeriods as $period)
                            <option value="{{ $period }}" {{ request('period') == $period ? 'selected' : '' }}>
                                {{ $period }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Bulan Filter -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select id="month" name="month" class="form-select w-full" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        @php
                            $months = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                        @endphp
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}" {{ request('month') == $key ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="form-select w-full" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @php
                            $statuses = ['Draft', 'Diajukan', 'Diperiksa', 'Diverifikasi', 'Disetujui', 'Ditolak', 'Selesai'];
                        @endphp
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Reset Button -->
            @if(request()->hasAny(['period', 'month', 'status']))
                <div class="mt-4">
                    <a href="{{ route('bapp.recap.index') }}" class="text-sm text-red-600 hover:text-red-800">
                        <i class="fas fa-times mr-1"></i> Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Periode BAPP</th>
                        <th>Bulan</th>
                        <th>Jumlah Vendor</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $counter = ($periods->currentPage() - 1) * $periods->perPage() + 1;
                    @endphp
                    @forelse($periods as $period)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="font-medium text-gray-900">{{ $counter++ }}</td>
                        <td class="font-medium text-gray-900">
                            {{ $period['period'] }}
                        </td>
                        <td class="text-gray-700">
                            {{ $period['month'] }}
                        </td>
                        <td>
                            <span class="vendor-badge">
                                {{ $period['total_vendors'] }} Vendor
                            </span>
                        </td>
                        <td class="font-medium text-gray-900">
                            Rp {{ number_format($period['total_amount'], 0, ',', '.') }}
                        </td>
                        <td>
                            @php
                                $status = $period['status'] ?? 'draft';
                                $statusClass = 'status-' . $status;
                                
                                // Ensure the status is properly capitalized for display
                                $displayStatus = match($status) {
                                    'draft' => 'Draft',
                                    'diajukan' => 'Diajukan',
                                    'diverifikasi' => 'Diverifikasi',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    'dibayar' => 'Selesai',
                                    'selesai' => 'Selesai',
                                    default => ucfirst($status)
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('bapp.recap.detail', $period['period']) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                               title="Lihat Detail">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="bg-gray-100 p-4 rounded-full mb-4">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data rekap</h3>
                                <p class="text-gray-500 mb-4">Silakan coba dengan filter yang berbeda</p>
                                @if(request()->hasAny(['period', 'month', 'status']))
                                    <a href="{{ route('bapp.recap.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Reset Filter
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($periods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $periods->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit form when filter changes
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[method="get"]');
        const filterSelects = filterForm.querySelectorAll('select');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>
@endpush
@endsection