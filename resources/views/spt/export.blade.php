<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Export Data SPT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Data Surat Perintah Tebang (SPT)</h2>
        <p>Tanggal Ekspor: {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. SPT</th>
                <th>Vendor</th>
                <th>Petak</th>
                <th>Nama Mandor</th>
                <th>Tanggal Tebang</th>
                <th>Jumlah Tenaga Kerja</th>
                <th>Jenis Tebang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spts as $index => $spt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $spt->kode_spt }}</td>
                <td>{{ $spt->vendor ? $spt->vendor->nama_vendor : '-' }}</td>
                <td>{{ $spt->kode_petak }}</td>
                <td>{{ $spt->foreman ? $spt->foreman->nama_mandor : $spt->kode_mandor }}</td>
                <td>{{ $spt->tanggal_tebang ? \Carbon\Carbon::parse($spt->tanggal_tebang)->format('d/m/Y') : '-' }}</td>
                <td>{{ $spt->jumlah_tenaga_kerja }}</td>
                <td>{{ $spt->jenis_tebang }}</td>
                <td>{{ $spt->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
