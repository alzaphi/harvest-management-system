<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>LKT {{ $lkt->kode_lkt }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        .document-title {
            margin: 20px 0;
        }
        .document-title h1 {
            font-size: 18px;
            margin: 0;
            padding: 0;
            text-decoration: underline;
        }
        .document-code {
            font-weight: bold;
            margin: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
        }
        .label {
            font-weight: bold;
            width: 30%;
            background-color: #f8f9fa;
        }
        .signature-box {
            text-align: center;
            margin-top: 30px;
            float: left;
            width: 33.33%;
            font-size: 11px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 40px auto 5px;
        }
        .signature-name {
            font-weight: bold;
            margin-top: 5px;
        }
        .signature-role {
            font-size: 11px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .logo {
            max-width: 80px;
            height: auto;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .company-info {
            margin-left: 20px;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .mb-4 {
            margin-bottom: 1rem;
        }
        .mt-4 {
            margin-top: 1rem;
        }
        .signature-img {
            max-height: 60px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo_jbm.png') }}" alt="JBM Logo" class="logo">
            <div class="company-info">
                <div class="company-name">PT. JHONLIN BATU MANDIRI</div>
                <div>Harvest Management System</div>
            </div>
        </div>
        <div class="document-title">
            <h1>LEMBAR KERJA TEBANG (LKT)</h1>
        </div>
        <div class="document-code">
            Nomor: {{ $lkt->kode_lkt }}
            @php
                $statusClass = '';
                if ($lkt->status === 'Disetujui') {
                    $statusClass = 'status-active';
                } elseif ($lkt->status === 'Diajukan' || $lkt->status === 'Diperiksa') {
                    $statusClass = 'status-pending';
                } else {
                    $statusClass = 'status-draft';
                }
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $lkt->status }}</span>
        </div>
    </div>

    <table>
        <tr>
            <td class="label">Tanggal Tebang</td>
            <td>{{ \Carbon\Carbon::parse($lkt->tanggal_tebang)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nomor SPT</td>
            <td>{{ $lkt->kode_spt }}</td>
        </tr>
        <tr>
            <td class="label">Vendor Tebang</td>
            <td>{{ $lkt->vendorTebang->nama_vendor ?? '-' }} ({{ $lkt->kode_vendor_tebang }})</td>
        </tr>
        @if($lkt->kode_vendor_angkut)
        <tr>
            <td class="label">Vendor Angkut</td>
            <td>{{ $lkt->vendorAngkut->nama_vendor ?? '-' }} ({{ $lkt->kode_vendor_angkut }})</td>
        </tr>
        @endif
        @if($lkt->kode_driver)
        <tr>
            <td class="label">Driver</td>
            <td>{{ $lkt->driver->nama_vendor ?? '-' }} ({{ $lkt->kode_driver }})</td>
        </tr>
        @endif
        <tr>
            <td class="label">Petak</td>
            <td>{{ $lkt->petak->kode_petak ?? '-' }} - {{ $lkt->petak->blok ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Tebangan</td>
            <td>{{ $lkt->jenis_tebangan ?? '-' }}</td>
        </tr>
        @if($lkt->tarif_zona_angkutan)
        <tr>
            <td class="label">Tarif Zona Angkutan</td>
            <td>Zona {{ $lkt->tarif_zona_angkutan }}</td>
        </tr>
        @endif
        @if($lkt->catatan)
        <tr>
            <td class="label">Catatan</td>
            <td>{{ $lkt->catatan }}</td>
        </tr>
        @endif
    </table>

    <div class="clearfix">
        <!-- Pembuat -->
        <div class="signature-box">
            @if($lkt->ttd_dibuat_oleh_path && file_exists(public_path('storage/' . $lkt->ttd_dibuat_oleh_path)))
                <img src="{{ public_path('storage/' . $lkt->ttd_dibuat_oleh_path) }}" alt="Tanda Tangan" class="signature-img">
            @else
                <div class="signature-line"></div>
            @endif
            <div class="signature-name">{{ $lkt->dibuat_oleh }}</div>
            <div class="signature-role">Pembuat</div>
            <div class="signature-role">{{ $lkt->ttd_dibuat_pada ? \Carbon\Carbon::parse($lkt->ttd_dibuat_pada)->format('d/m/Y H:i') : '' }}</div>
        </div>

        <!-- Pemeriksa -->
        <div class="signature-box">
            @if($lkt->ttd_diperiksa_oleh_path && file_exists(public_path('storage/' . $lkt->ttd_diperiksa_oleh_path)))
                <img src="{{ public_path('storage/' . $lkt->ttd_diperiksa_oleh_path) }}" alt="Tanda Tangan" class="signature-img">
            @else
                <div class="signature-line"></div>
            @endif
            <div class="signature-name">{{ $lkt->diperiksa_oleh ?? '-' }}</div>
            <div class="signature-role">Pemeriksa</div>
            <div class="signature-role">{{ $lkt->ttd_diperiksa_pada ? \Carbon\Carbon::parse($lkt->ttd_diperiksa_pada)->format('d/m/Y H:i') : '' }}</div>
        </div>

        <!-- Penyetuju -->
        <div class="signature-box">
            @if($lkt->ttd_disetujui_oleh_path && file_exists(public_path('storage/' . $lkt->ttd_disetujui_oleh_path)))
                <img src="{{ public_path('storage/' . $lkt->ttd_disetujui_oleh_path) }}" alt="Tanda Tangan" class="signature-img">
            @else
                <div class="signature-line"></div>
            @endif
            <div class="signature-name">{{ $lkt->disetujui_oleh ?? '-' }}</div>
            <div class="signature-role">Penyetuju</div>
            <div class="signature-role">{{ $lkt->ttd_disetujui_pada ? \Carbon\Carbon::parse($lkt->ttd_disetujui_pada)->format('d/m/Y H:i') : '' }}</div>
        </div>
    </div>

    <!-- Timbangan -->
    @if($lkt->ttd_ditimbang_oleh_path || $lkt->ditimbang_oleh)
    <div class="clearfix mt-4">
        <div class="signature-box">
            @if($lkt->ttd_ditimbang_oleh_path && file_exists(public_path('storage/' . $lkt->ttd_ditimbang_oleh_path)))
                <img src="{{ public_path('storage/' . $lkt->ttd_ditimbang_oleh_path) }}" alt="Tanda Tangan" class="signature-img">
            @else
                <div class="signature-line"></div>
            @endif
            <div class="signature-name">{{ $lkt->ditimbang_oleh ?? '-' }}</div>
            <div class="signature-role">Petugas Timbang</div>
            <div class="signature-role">{{ $lkt->ttd_ditimbang_pada ? \Carbon\Carbon::parse($lkt->ttd_ditimbang_pada)->format('d/m/Y H:i') : '' }}</div>
        </div>
    </div>
    @endif

    <div class="footer" style="margin-top: 50px; font-size: 10px; text-align: center;">
        <div>Dokumen ini dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>
