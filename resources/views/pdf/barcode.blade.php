@php
    $header = 'Barcode';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Barcode']
    ];
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode Vendor - {{ $user->vendor->nama_vendor ?? $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            color: #1a56db;
            margin: 10px 0;
            font-size: 20px;
        }
        .barcode-container {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #eee;
        }
        .vendor-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
        }
        .vendor-info h2 {
            color: #1a56db;
            font-size: 16px;
            margin: 5px 0 10px 0;
        }
        .qr-code {
            margin: 15px auto;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .barcode-url {
            word-break: break-all;
            font-size: 11px;
            color: #666;
            margin: 10px 0;
            padding: 8px;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Barcode Vendor</h1>
            <p>Dokumen ini berisi informasi barcode untuk LKT vendor</p>
        </div>

        <div class="vendor-info">
            <h2>Informasi Vendor</h2>
            <p><strong>Nama Vendor:</strong> {{ $user->vendor->nama_vendor ?? $user->name }}</p>
            @if(isset($user->vendor->all_kode_vendor) && $user->vendor->all_kode_vendor !== 'N/A')
                <p><strong>Kode Vendor:</strong> {{ $user->vendor->all_kode_vendor }}</p>
            @else
                <p><strong>Kode Vendor:</strong> {{ $user->vendor->kode_vendor ?? 'N/A' }}</p>
            @endif
            <p><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="barcode-container">
            <h3>Barcode Verifikasi</h3>
            <div class="qr-code">
                @if(strpos($qrCodePdf, 'data:image/svg+xml;base64,') === 0)
                    <img src="{{ $qrCodePdf }}" alt="QR Code" style="width:200px;height:200px;display:block;margin:0 auto;">
                @else
                    {!! $qrCodePdf !!}
                @endif
            </div>
            <p>Scan barcode di atas untuk verifikasi LKT vendor</p>
            <div class="barcode-url">
                {{ $url }}
            </div>
        </div>

        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
            <p>© {{ date('Y') }} PT. Jhonlin Batu Mandiri</p>
        </div>
    </div>
</body>
</html>