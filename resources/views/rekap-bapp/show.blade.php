@extends('layouts.master')

@section('page-title', 'Detail Rekap BAPP')


@push('styles')
<style>
    .bapp-container {
        padding: 1rem 2rem;
        background-color: #fff;
        color: #000;
    }
    .bapp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .bapp-header img {
        width: 120px;
    }
    .company-info {
        text-align: left;
        flex: 1;
        margin-left: 1rem;
    }
    .document-title {
        text-align: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 1rem 0;
    }
    .bapp-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    .bapp-table th, .bapp-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }
    .bapp-table th {
        background-color: #f3f4f6;
    }
    .text-right {
        text-align: right;
    }
    .info-box {
        margin: 1rem 0;
        border: 1px solid #000;
        padding: 0.75rem;
    }
    .info-row {
        display: flex;
        margin-bottom: 0.5rem;
    }
    .info-label {
        width: 180px;
        font-weight: bold;
    }
    .info-value {
        flex: 1;
    }
    .info-value .badge {
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

    .info-value .bg-warning {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a !important;
    }

    .info-value .bg-info {
        background-color: #dbeafe !important;
        color: #1e40af !important;
        border-color: #bfdbfe !important;
    }

    .info-value .bg-success {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        border-color: #86efac !important;
    }

    .info-value .bg-danger {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border-color: #fca5a5 !important;
    }

    .info-value .bg-primary {
        background-color: #e0f2fe !important;
        color: #075985 !important;
        border-color: #7dd3fc !important;
    }

    .info-value .bg-secondary {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
        border-color: #e5e7eb !important;
    }
    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
    }
    .button-group .left-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .button-group .right-buttons {
        display: flex;
        gap: 0.5rem;
    }
    @media print {
        .no-print, .button-group {
            display: none !important;
        }
        body * {
            visibility: hidden;
        }
        .bapp-container, .bapp-container * {
            visibility: visible;
        }
        .bapp-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid bapp-container">
    <div class="bapp-header">
        <img src="{{ asset('images/logo jbm.png') }}" alt="JBM Logo">
        <div class="company-info">
            <h3>JHONLIN BATU MANDIRI</h3>
            <p>REKAPITULASI PERHITUNGAN BAPP TEBANG DAN ANGKUT</p>
        </div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Nomor/Kode Rekap</div>
            <div class="info-value">: JBM/REKAPBAPP/{{ strtoupper($bapp->periode_bapp ?? $period) }}/{{ date('Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Periode Rekap</div>
           <div class="info-value">: {{ $period }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Rekap BAPP</div>
            <div class="info-value">: {{ now()->format('d F Y') }}</div>
        </div>
        @if($spdStatus)
        <div class="info-row">
            <div class="info-label">Status SPD</div>
            <div class="info-value">: 
                @php
                    $statusClass = '';
                    $displayStatus = '';
                    
                    switch(strtolower($spdStatus)) {
                        case 'draft':
                            $statusClass = 'bg-secondary';
                            $displayStatus = 'Draft';
                            break;
                        case 'diajukan':
                            $statusClass = 'bg-info';
                            $displayStatus = 'Diajukan';
                            break;
                        case 'diverifikasi':
                            $statusClass = 'bg-warning';
                            $displayStatus = 'Diverifikasi';
                            break;
                        case 'disetujui':
                            $statusClass = 'bg-success';
                            $displayStatus = 'Disetujui';
                            break;
                        case 'ditolak':
                            $statusClass = 'bg-danger';
                            $displayStatus = 'Ditolak';
                            break;
                        case 'dibayar':
                        case 'selesai':
                            $statusClass = 'bg-primary';
                            $displayStatus = 'Selesai';
                            break;
                        default:
                            $statusClass = 'bg-secondary';
                            $displayStatus = $spdStatus;
                    }
                @endphp
                <span class="badge {{ $statusClass }}">{{ $displayStatus }}</span>
                @if($spdStatus === 'Dibayar' && $spd)
                    <small class="text-muted">({{ $spd->dibayar_pada ? 'Dibayar pada ' . \Carbon\Carbon::parse($spd->dibayar_pada)->format('d/m/Y') : '' }})</small>
                @endif
            </div>
        </div>
        @endif
    </div>

    <table class="bapp-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode BAPP</th>
                <th>Kode Vendor</th>
                <th>Nama Vendor</th>
                <th>Jenis Vendor</th>
                <th>Total Tonase</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalTonase = 0;
                $grandTotalPendapatan = 0;
                $vendors = $vendors->sortBy('vendor.kode_vendor');
            @endphp
            
            @foreach($vendors as $index => $vendorData)
                @php
                    $vendor = $vendorData['vendor'];
                    $totalTonase = $vendorData['total_tonase'] ?? 0;
                    $totalPendapatan = $vendorData['total_pendapatan'] ?? 0;
                    $type = $vendorData['type'] ?? 'tebang';
                    $grandTotalTonase += $totalTonase;
                    $grandTotalPendapatan += $totalPendapatan;
                    
                    // Dapatkan data vendor dari tabel vendor_angkut
                    $vendorModel = \App\Models\VendorAngkut::where('kode_vendor', $vendor->kode_vendor)->first();
                    
                    $namaVendor = $vendorModel ? $vendorModel->nama_vendor : 'Vendor Tidak Ditemukan';
                    $jenisVendor = $vendorModel ? $vendorModel->jenis_vendor : 'Angkut';
                    
                    // Get BAPP codes for this vendor
                    $bappModel = $type === 'tebang' ? \App\Models\BappTebang::class : \App\Models\BappAngkut::class;
                    $bappCodes = $bappModel::where('periode_bapp', $period)
                        ->where($type === 'tebang' ? 'vendor_tebang' : 'vendor_angkut', $vendor->kode_vendor)
                        ->pluck('kode_bapp')
                        ->unique()
                        ->values()
                        ->toArray();
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if(!empty($bappCodes))
                            {{ implode(', ', $bappCodes) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $vendor->kode_vendor ?? '-' }}</td>
                    <td>{{ $namaVendor }}</td>
                    <td>{{ ucfirst($jenisVendor) }}</td>
                    <td class="text-right">{{ number_format($totalTonase, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            <tr style="font-weight: bold;">
                <td colspan="5" style="text-align: right;">TOTAL</td>
                <td class="text-right">{{ number_format($grandTotalTonase, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="button-group no-print">
        <div class="left-buttons">
            <a href="{{ route('bapp.recap.index') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="right-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
            @if($spdStatus && $spdStatus !== 'Draft')
                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('Manager Plantation'))
                    @if($spdStatus === 'Ditolak')
                        <a href="{{ route('bapp.recap.spd', ['period' => $period]) }}" class="btn btn-success">
                            <i class="fas fa-file-invoice-dollar"></i> Ajukan Ulang Dana
                        </a>
                    @endif
                @endif
                <a href="{{ route('spd.show', $spd->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Lihat Status SPD
                </a>
            @elseif((!$spdStatus || $spdStatus === 'Draft') && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Manager Plantation')))
                <a href="{{ route('bapp.recap.spd', ['period' => $period]) }}" class="btn btn-success">
                    <i class="fas fa-file-invoice-dollar"></i> Ajukan Dana
                </a>
            @endif
        </div>
    </div>
</div>
@endsection