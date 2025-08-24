<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>LAPORAN LEMBAR KERJA TEBANG (LKT)</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2, .header p {
            margin: 2px 0;
            padding: 0;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
        }
        .header p {
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .small-text {
            font-size: 8pt;
        }
        .no-border {
            border: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN LEMBAR KERJA TEBANG (LKT)</h2>
        <p>Tanggal Cetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 10%;">No. LKT</th>
                <th rowspan="2" style="width: 10%;">No. SPT</th>
                <th rowspan="2" style="width: 8%;">Tgl Tebang</th>
                <th rowspan="2" style="width: 12%;">Vendor Tebang</th>
                <th rowspan="2" style="width: 12%;">Vendor Angkut</th>
                <th rowspan="2" style="width: 12%;">Driver</th>
                <th rowspan="2" style="width: 8%;">Petak</th>
                <th rowspan="2" style="width: 5%;">Zona</th>
                <th rowspan="2" style="width: 8%;">Status</th>
                <th colspan="4" style="width: 11%;">Penandatangan</th>
            </tr>
            <tr>
                <th style="width: 4%;">1</th>
                <th style="width: 4%;">2</th>
                <th style="width: 4%;">3</th>
                <th style="width: 4%;">4</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lkts as $index => $lkt)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $lkt->kode_lkt }}</td>
                <td>{{ $lkt->kode_spt }}</td>
                <td class="text-center">
                    @if($lkt->tanggal_tebang)
                        {{ \Carbon\Carbon::parse($lkt->tanggal_tebang)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $lkt->vendorTebang ? $lkt->vendorTebang->nama_vendor : '-' }}</td>
                <td>{{ $lkt->vendorAngkut ? $lkt->vendorAngkut->nama_vendor : '-' }}</td>
                <td>{{ $lkt->driver ? $lkt->driver->nama_vendor : '-' }}</td>
                <td class="text-center">{{ $lkt->kode_petak ?? '-' }}</td>
                <td class="text-center">{{ $lkt->tarif_zona_angkutan ?? '-' }}</td>
                <td class="text-center">
                    @if($lkt->status === 'Ditolak')
                        <span style="color: red;">Ditolak</span>
                    @elseif($lkt->status === 'Selesai')
                        <span style="color: green;">Selesai</span>
                    @else
                        {{ $lkt->status }}
                    @endif
                </td>
                <td class="text-center">
                    @if($lkt->dibuat_oleh)
                        ✓<br>
                        <small>{{ $lkt->dibuat_oleh }}</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($lkt->status === 'Ditolak')
                        <span style="color: red;">×</span>
                    @elseif($lkt->status === 'Diperiksa' || $lkt->status === 'Disetujui' || $lkt->status === 'Selesai')
                        ✓<br>
                        <small>{{ $lkt->diperiksa_oleh ?? 'P1' }}</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($lkt->status === 'Ditolak')
                        <span style="color: red;">×</span>
                    @elseif($lkt->status === 'Disetujui' || $lkt->status === 'Selesai')
                        ✓<br>
                        <small>{{ $lkt->disetujui_oleh ?? 'P2' }}</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($lkt->status === 'Selesai')
                        ✓<br>
                        <small>{{ $lkt->ditimbang_oleh ?? 'Timbangan' }}</small>
                    @elseif($lkt->status === 'Ditolak')
                        <span style="color: red;">×</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 8pt;">
        <p>Keterangan:</p>
        <p>1. Kolom penandatangan diisi dengan tanda centang (✓) jika sudah ditandatangani</p>
        <p>2. Status: Draft, Diajukan, Disetujui, Ditolak</p>
    </div>
</body>
</html>
