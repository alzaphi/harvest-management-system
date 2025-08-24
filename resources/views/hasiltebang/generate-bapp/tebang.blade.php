@extends('layouts.master')

@section('page-title', 'BAPP Tebang - ' . $vendor->nama_vendor)

@push('styles')
<style>
    .bapp-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .bapp-header {
        text-align: center;
        margin-bottom: 2rem;
        border-bottom: 2px solid #333;
        padding-bottom: 1rem;
    }
    .bapp-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    .bapp-subtitle {
        font-size: 1.1rem;
        color: #555;
    }
    .bapp-info {
        margin-bottom: 2rem;
    }
    .info-row {
        display: flex;
        margin-bottom: 0.5rem;
    }
    .info-label {
        font-weight: 600;
        width: 200px;
    }
    .info-value {
        flex: 1;
    }
    .bapp-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }
    .bapp-table th, .bapp-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .bapp-table th {
        background-color: #f5f5f5;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .signature-section {
        margin-top: 3rem;
        display: flex;
        justify-content: space-between;
    }
    .signature-box {
        text-align: center;
        width: 200px;
    }
    .signature-line {
        border-top: 1px solid #000;
        margin: 50px auto 10px;
        width: 80%;
    }
    .print-button {
        margin-top: 2rem;
        text-align: center;
    }
    @media print {
        .no-print {
            display: none;
        }
        body {
            padding: 0;
            background: white;
        }
        .bapp-container {
            box-shadow: none;
            padding: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="bapp-container">
    <div class="bapp-header">
        <div class="bapp-title">BERITA ACARA PEMERIKSAAN HASIL TEBANG</div>
        <div class="bapp-subtitle">(BAPP Tebang)</div>
    </div>

    <div class="bapp-info">
        <div class="info-row">
            <div class="info-label">Nomor BAPP</div>
            <div class="info-value">: BAPP-{{ strtoupper(Str::random(8)) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div class="info-value">: {{ $tanggal }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Vendor Tebang</div>
            <div class="info-value">: {{ $vendor->kode_vendor }} - {{ $vendor->nama_vendor }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Alamat</div>
            <div class="info-value">: {{ $vendor->alamat ?? '-' }}</div>
        </div>
    </div>

    <table class="bapp-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Hasil Tebang</th>
                <th>Tanggal Tebang</th>
                <th>Petak</th>
                <th>Jenis Kayu</th>
                <th>Volume (m³)</th>
                <th>Bruto (kg)</th>
                <th>Tarra (kg)</th>
                <th>Netto (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_hasil_tebang }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_tebang)->format('d/m/Y') }}</td>
                <td>{{ $item->kode_petak }}</td>
                <td>{{ $item->jenis_kayu ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->volume, 2) }}</td>
                <td class="text-right">{{ number_format($item->bruto, 2) }}</td>
                <td class="text-right">{{ number_format($item->tarra, 2) }}</td>
                <td class="text-right">{{ number_format($item->netto2, 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($items->sum('volume'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total_bruto, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total_tarra, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total_netto, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div>Menyetujui,</div>
            <div>Manager</div>
            <div class="signature-line"></div>
            <div>(___________________)</div>
        </div>
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div>Admin</div>
            <div class="signature-line"></div>
            <div>(___________________)</div>
        </div>
        <div class="signature-box">
            <div>Penerima,</div>
            <div>Vendor</div>
            <div class="signature-line"></div>
            <div>(___________________)</div>
        </div>
    </div>

    <div class="print-button no-print">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Cetak BAPP
        </button>
        <a href="{{ route('bapp.index') }}" class="ml-4 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Kembali ke Rekap BAPP
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto print when page loads (optional)
    // window.onload = function() {
    //     window.print();
    // };
</script>
@endpush
