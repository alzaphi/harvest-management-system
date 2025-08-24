<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SPT {{ $spt->kode_spt }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
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
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
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
            width: 30%;
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
            font-size: 12px;
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
            margin-left: 15px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo jbm.png') }}" alt="JBM Logo" class="logo">
            <div class="company-info">
                <div class="company-name">PT. JHONLIN BATU MANDIRI</div>
                <div class="company-subtitle">Harvest Management System</div>
            </div>
        </div>

        <div class="document-title">
            <h1>SURAT PERINTAH TEBANG</h1>
            <div class="document-code">{{ $spt->kode_spt }}</div>
            @php
                $status = $spt->status ?? 'Draft';
                $statusClass = [
                    'Disetujui' => 'status-active',
                    'Diajukan' => 'status-pending',
                    'Draft' => 'status-draft'
                ][$status] ?? 'status-draft';
            @endphp
            <span class="status-badge {{ $statusClass }}">
                {{ $status }}
            </span>
        </div>
    </div>

    <!-- Main Content Table -->
    <table>
        <tr>
            <td class="label">Nomor SPT</td>
            <td>{{ $spt->kode_spt }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Tebang</td>
            <td>{{ \Carbon\Carbon::parse($spt->tanggal_tebang)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Vendor Tebang</td>
            <td>
                @if($spt->vendor)
                    {{ $spt->vendor->nama_vendor }} ({{ $spt->kode_vendor_tebang ?? $spt->kode_vendor }})
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Kode Petak</td>
            <td>{{ $spt->kode_petak ?? '-' }}</td>
        </tr>
        @if($spt->subBlock)
        <tr>
            <td class="label">Detail Petak</td>
            <td class="whitespace-nowrap">
                {{ $spt->subBlock->estate ?? '-' }} /
                {{ $spt->subBlock->divisi ?? '-' }} /
                {{ $spt->subBlock->blok ?? '-' }} /
                {{ $spt->subBlock->luas_area ? number_format($spt->subBlock->luas_area, 2, ',', '.') . ' Ha' : '-' }}
                @if($spt->subBlock->zona)
                    / {{ $spt->subBlock->zona }}
                @endif
            </td>
        </tr>
        @endif
        <tr>
            <td class="label">Diawasi Oleh</td>
            <td>
                @if($spt->foremanSubBlock)
                    {{ $spt->foremanSubBlock->nama_mandor }} ({{ $spt->foremanSubBlock->kode_mandor }})
                @else
                    {{ $spt->kode_mandor ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Jumlah Tenaga</td>
            <td>{{ $spt->jumlah_tenaga_kerja ?? $spt->jumlah_tenaga }} orang</td>
        </tr>
        <tr>
            <td class="label">Jenis Tebang</td>
            <td>{{ $spt->jenis_tebang }}</td>
        </tr>
        @if($spt->catatan)
        <tr>
            <td class="label">Catatan</td>
            <td>{{ $spt->catatan }}</td>
        </tr>
        @endif
    </table>

    <!-- Signatures -->
    <div class="clearfix">
        <!-- Pembuat -->
        <div class="signature-box">
            @if($spt->ttd_dibuat_oleh_path && file_exists(public_path('storage/' . $spt->ttd_dibuat_oleh_path)))
                <img src="{{ public_path('storage/' . $spt->ttd_dibuat_oleh_path) }}" alt="Tanda Tangan" style="max-height: 80px;">
            @else
                <div style="height: 80px; display: flex; align-items: flex-end;">
                    <div style="width: 150px; height: 1px; border-top: 1px solid #000; margin: 0 auto;"></div>
                </div>
            @endif
            <div class="signature-line"></div>
            <div class="signature-name">{{ $spt->dibuat_oleh }}</div>
            <div class="signature-role">Pembuat</div>
        </div>

        <!-- Pemeriksa -->
        <div class="signature-box">
            @if($spt->ttd_diperiksa_oleh_path && file_exists(public_path('storage/' . $spt->ttd_diperiksa_oleh_path)))
                <img src="{{ public_path('storage/' . $spt->ttd_diperiksa_oleh_path) }}" alt="Tanda Tangan" style="max-height: 80px;">
            @else
                <div style="height: 80px; display: flex; align-items: flex-end;">
                    <div style="width: 150px; height: 1px; border-top: 1px solid #000; margin: 0 auto;"></div>
                </div>
            @endif
            <div class="signature-line"></div>
            <div class="signature-name">{{ $spt->diperiksa_oleh }}</div>
            <div class="signature-role">Pemeriksa</div>
        </div>

        <!-- Penyetuju -->
        <div class="signature-box">
            @if($spt->ttd_disetujui_oleh_path && file_exists(public_path('storage/' . $spt->ttd_disetujui_oleh_path)))
                <img src="{{ public_path('storage/' . $spt->ttd_disetujui_oleh_path) }}" alt="Tanda Tangan" style="max-height: 80px;">
            @else
                <div style="height: 80px; display: flex; align-items: flex-end;">
                    <div style="width: 150px; height: 1px; border-top: 1px solid #000; margin: 0 auto;"></div>
                </div>
            @endif
            <div class="signature-line"></div>
            <div class="signature-name">{{ $spt->disetujui_oleh }}</div>
            <div class="signature-role">Penyetuju</div>
        </div>
    </div>

    <div style="clear: both; margin-top: 20px; font-size: 12px; text-align: center; color: #666;">
        Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }}
    </div>
</body>
</html>
