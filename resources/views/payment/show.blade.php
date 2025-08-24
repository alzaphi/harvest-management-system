@extends('layouts.master')

@section('page-title', 'Detail SPD - ' . $spd->no_spd)

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
    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
    }
    .button-group .left-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .signature-box {
        margin-top: 2rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .signature {
        text-align: center;
        margin: 1rem 0;
        flex: 1;
        min-width: 200px;
    }
    .signature-line {
        border-top: 1px solid #000;
        margin: 2rem auto 0.5rem;
        width: 80%;
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
            <div class="info-label">No. SPD</div>
            <div class="info-value">: {{ $spd->no_spd }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Periode</div>
            <div class="info-value">: {{ $spd->periode }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal SPD</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($spd->tanggal_spd)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Dana</div>
            <div class="info-value">: Rp {{ number_format($spd->total_dana, 0, ',', '.') }} ({{ $terbilang }})</div>
        </div>
    </div>

    <table class="bapp-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Vendor</th>
                <th>Nama Vendor</th>
                <th>Jenis Vendor</th>
                <th>No. Rekening</th>
                <th>Total Tonase</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalTonase = 0;
                $grandTotalPendapatan = 0;
            @endphp
            
            @foreach($vendors as $index => $vendorData)
                @php
                    $vendor = $vendorData['vendor'];
                    $totalTonase = $vendorData['total_tonase'] ?? 0;
                    $totalPendapatan = $vendorData['total_pendapatan'] ?? 0;
                    $type = $vendorData['type'] ?? 'tebang';
                    $grandTotalTonase += $totalTonase;
                    $grandTotalPendapatan += $totalPendapatan;
                    
                    // Get vendor details including bank account
                    $vendorModel = $vendorData['vendor_model'] ?? null;
                    $rekening = $vendorModel ? $vendorModel->nomor_rekening : '-';
                    $namaVendor = $vendor ? $vendor->nama_vendor : 'Vendor Tidak Ditemukan';
                    $kodeVendor = $vendor ? $vendor->kode_vendor : '-';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $kodeVendor }}</td>
                    <td>{{ $namaVendor }}</td>
                    <td>{{ ucfirst($type) }}</td>
                    <td>{{ $rekening }}</td>
                    <td class="text-right">{{ number_format($totalTonase, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            <tr style="font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($grandTotalTonase, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
<!-- 
    Tanda Tangan
    <div class="signature-box">
        <div class="signature">
            <div>Diajukan Oleh:</div>
            <div class="signature-line"></div>
            <div>{{ $spd->diajukanOleh->name ?? '-' }}</div>
            <div>{{ $spd->tanggal_diajukan ? \Carbon\Carbon::parse($spd->tanggal_diajukan)->format('d/m/Y') : '-' }}</div>
        </div>

        <div class="signature">
            <div>Diverifikasi Oleh:</div>
            <div class="signature-line"></div>
            <div>{{ $spd->diverifikasiOleh->name ?? '-' }}</div>
            <div>{{ $spd->tanggal_diverifikasi ? \Carbon\Carbon::parse($spd->tanggal_diverifikasi)->format('d/m/Y') : '-' }}</div>
        </div>

        <div class="signature">
            <div>Disetujui Oleh:</div>
            <div class="signature-line"></div>
            <div>{{ $spd->disetujuiOleh->name ?? '-' }}</div>
            <div>{{ $spd->tanggal_disetujui ? \Carbon\Carbon::parse($spd->tanggal_disetujui)->format('d/m/Y') : '-' }}</div>
        </div>
    </div> -->

    <div class="button-group no-print">
        <div class="left-buttons">
            <a href="{{ route('payment.index') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection