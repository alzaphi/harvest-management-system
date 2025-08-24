@extends('layouts.master')

@section('page-title', 'Berita Acara Penerimaan dan Pemeriksaan (BAPP) Angkut')

@php
    $header = 'Approval BAPP';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Detail BAPP', 'url' => route('bapp.approval.index')],    ];
@endphp

@push('styles')
<style>
    .bapp-container {
        padding: 1rem 2rem;
        background-color: #fff;
        color: #000;
        position: relative;
    }
    .bapp-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        margin-bottom: 1rem;
    }
    .header-left {
        display: flex;
        align-items: flex-start;
        flex: 1;
    }
    .bapp-header img {
        width: 120px;
        margin-right: 1rem;
    }
    .company-info {
        text-align: left;
        flex: 1;
    }
    .document-title {
        text-align: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 0.5rem 0;
        position: relative;
    }
    .status-badge-container {
        position: absolute;
        top: 10px;
        right: 20px;
        z-index: 100;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-draft {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .status-completed {
        background-color: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }
    .bapp-table, .rekap-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    .bapp-table th, .bapp-table td,
    .rekap-table th, .rekap-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }
    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 3rem;
    }
    .signature-box {
        text-align: center;
        width: 20%;
    }
    .document-title {
        font-weight: bold;
        font-size: 1.2rem;
        margin: 0.5rem 0;
        position: relative;
    }
    .status-badge-container {
        position: absolute;
        top: 10px;
        right: 20px;
        z-index: 100;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-draft {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .status-completed {
        background-color: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            font-size: 12px;
        }
        .bapp-container {
            padding: 0.5rem;
        }
        .status-badge-container {
            top: 5px;
            right: 10px;
        }
    }
    .bapp-table, .rekap-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    .bapp-table th, .bapp-table td,
    .rekap-table th, .rekap-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }
    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 3rem;
    }
    .signature-pad {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
        max-width: 100%;
        width: 300px;
        height: 150px;
        margin: 0 auto;
    }
    .signature-buttons {
        margin-top: 10px;
        text-align: center;
    }
    .signature-box {
        text-align: center;
        width: 30%;
        margin: 0 1%;
    }
    .signature-line {
        width: 100%;
        height: 1px;
        background-color: #000;
        margin: 5px 0;
    }
    .signature-role {
        font-size: 12px;
        color: #6c757d;
    }
    .modal-content {
        border-radius: 0.8rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }
    .modal-header {
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    .modal-footer {
        border-top: none;
        padding: 0 1.5rem 1.5rem;
    }
    .btn-close:focus {
        box-shadow: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid bapp-container">
    <!-- Status Badge - Top Right Corner -->
    <div class="status-badge-container">
        @php
            $status = $bapp->status ?? 'Draft';
            $statusClass = [
                'Diajukan' => 'status-pending',
                'Diperiksa' => 'status-pending',
                'Diverifikasi' => 'status-pending',
                'Disetujui' => 'status-active',
                'Ditolak' => 'status-rejected',
                'Selesai' => 'status-completed',
                'Draft' => 'status-draft'
            ][$status] ?? 'status-draft';
        @endphp
        <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
    </div>

    <div class="bapp-header">
        <div class="header-left">
            <img src="{{ asset('images/logo jbm.png') }}" alt="JBM Logo">
            <div class="company-info">
                <h3>JHONLIN BATU MANDIRI</h3>
                <p>KARTU UPAH ANGKUTAN</p>
            </div>
        </div>
    </div>

    <div class="document-title">
        BERITA ACARA PENERIMAAN DAN PEMERIKSAAN (BAPP) ANGKUT
    </div>

    <table style="width: 100%; margin-top: 1rem; font-size: 0.9rem;">
        <tr>
            <td style="width: 15%;">NAMA VENDOR</td>
            <td>: {{ $bapp->vendor ? $bapp->vendor->nama_vendor : ($bapp->vendor_angkut ?? '-') }}</td>
            <td style="width: 20%;">PERIODE BAPP</td>
            <td>: {{ $bapp->periode_bapp }}</td>
        </tr>
        <tr>
            <td>NOMOR</td>
            <td>: {{ $bapp->kode_bapp }}</td>
            <td>TANGGAL BAPP</td>
            <td>: {{ \Carbon\Carbon::parse($bapp->tanggal_bapp)->format('d F Y') }}</td>
        </tr>
    </table>



    <!-- TABEL DETAIL -->
    <table class="bapp-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Hasil Tebang</th>
                <th>Jenis Tebang</th>
                <th>Estate/Divisi</th>
                <th>Petak</th>
                <th>Kode Lambung</th>
                <th>Zonasi</th>
                <th>Tonase</th>
                <th>Sortase</th>
                <th>Tonase Final</th>
                <th>Angkut</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Get all BAPP Angkut with the same kode_bapp
                $bappAngkutList = \App\Models\BappAngkut::where('kode_bapp', $bapp->kode_bapp)
                    ->with('hasilTebang')
                    ->get();
            @endphp
            @foreach($bappAngkutList as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode_hasil_tebang }}</td>
                <td>{{ $item->jenis_tebang }}</td>
                <td>{{ $item->divisi }}</td>
                <td>{{ $item->kode_petak }}</td>
                <td>{{ $item->kode_lambung }}</td>
                <td>{{ $item->zonasi }}</td>
                <td>{{ number_format($item->tonase, 2, ',', '.') }}</td>
                <td>{{ number_format($item->sortase, 2, ',', '.') }}</td>
                <td>{{ number_format($item->tonase_final, 2, ',', '.') }}</td>
                <td>Rp {{ number_format($item->ongkos_angkut, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td colspan="7" style="text-align: center;">TOTAL</td>
                <td>{{ number_format($bappAngkutList->sum('tonase'), 2, ',', '.') }}</td>
                <td>{{ number_format($bappAngkutList->sum('sortase'), 2, ',', '.') }}</td>
                <td>{{ number_format($bappAngkutList->sum('tonase_final'), 2, ',', '.') }}</td>
                <td>Rp {{ number_format($bappAngkutList->sum('ongkos_angkut'), 0, ',', '.') }}</td>
                <td>Rp {{ number_format($bappAngkutList->sum('total_pendapatan'), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- REKAP TONASE ANGKUT -->
    <h4 style="margin-top: 1rem;">REKAP TONASE ANGKUT</h4>
    <table class="rekap-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Estate/Divisi</th>
                <th>Kode Lambung</th>
                <th>Zonasi</th>
                <th>Tonase Final</th>
                <th>Rupiah</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grouped = [];
                foreach ($bappAngkutList as $d) {
                    $key = $d->divisi . '|' . $d->kode_lambung . '|' . $d->zonasi;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'divisi' => $d->divisi,
                            'kode_lambung' => $d->kode_lambung,
                            'zonasi' => $d->zonasi,
                            'tonase_final' => 0,
                            'rupiah' => 0,
                        ];
                    }
                    $grouped[$key]['tonase_final'] += $d->tonase_final;
                    $grouped[$key]['rupiah'] += $d->total_pendapatan;
                }
                $grouped = array_values($grouped);
            @endphp

            @foreach($grouped as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['divisi'] }}</td>
                <td>{{ $row['kode_lambung'] }}</td>
                <td>{{ $row['zonasi'] }}</td>
                <td>{{ number_format($row['tonase_final'], 2, ',', '.') }}</td>
                <td>Rp {{ number_format($row['rupiah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td colspan="4" style="text-align: center;">JUMLAH</td>
                <td>{{ number_format(array_sum(array_column($grouped, 'tonase_final')), 2, ',', '.') }}</td>
                <td>Rp {{ number_format(array_sum(array_column($grouped, 'rupiah')), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if(true) <!-- Always show signature structure for viewing -->
    <!-- STRUKTUR Form Tanda Tangan -->


    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Tanda Tangan Digital</h5>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; margin: 2rem 0;">
                @php
                    $signatureData = [
                        [
                            'title' => 'Diajukan Oleh',
                            'signature_field' => 'ttd_diajukan_oleh_path',
                            'name_field' => 'diajukan_oleh',
                            'position' => 'Ast. Mgr. Plantation',
                            'width' => '30%',
                            'approval_role' => 'pengaju',
                            'approval_status' => 'Diajukan'
                        ],
                        [
                            'title' => 'Diperiksa Oleh',
                            'signature_field' => 'ttd_diperiksa_oleh_path',
                            'name_field' => 'diperiksa_oleh',
                            'position' => 'Vendor',
                            'width' => '30%',
                            'approval_role' => 'pemeriksa',
                            'approval_status' => 'Diperiksa'
                        ],
                        [
                            'title' => 'Disetujui Oleh',
                            'signature_field' => 'ttd_disetujui_oleh_path',
                            'name_field' => 'disetujui_oleh',
                            'position' => 'Mgr. Plantation',
                            'width' => '30%',
                            'approval_role' => 'penyetuju',
                            'approval_status' => 'Disetujui'
                        ]
                    ];
                @endphp

                @foreach($signatureData as $data)
                    <div style="width: {{ $data['width'] }}; text-align: center;">
                        <div style="border-top: 1px solid #000; margin: 0 auto; width: 80%;"></div>
                        <div style="min-height: 100px; margin: 5px 0; position: relative;">
                            @if (!empty($bapp->{$data['signature_field']}) && Storage::disk('public')->exists($bapp->{$data['signature_field']}))
                                <img src="{{ asset('storage/' . $bapp->{$data['signature_field']}) }}"
                                     alt="Tanda Tangan {{ $data['title'] }}"
                                     style="max-width: 100%; max-height: 80px; margin: 0 auto;">
                            @elseif(isset($isApproval) &&
                                   (($data['approval_role'] === 'pemeriksa' && in_array($bapp->status, ['Diajukan', 'Diperiksa']) && (auth()->user()->hasRole('vendor') || auth()->user()->hasRole('admin'))) ||
                                    ($data['approval_role'] === 'penyetuju' && $bapp->status === 'Diperiksa' && (auth()->user()->hasRole('manager-plantation') || auth()->user()->hasRole('admin')))))
                                <!-- Signature Pad Container -->
                                <div class="signature-container" id="signature-container-{{ $data['approval_role'] }}">
                                    <div class="signature-pad" id="signature-pad-{{ $data['approval_role'] }}" style="border: 1px solid #ddd; width: 100%; height: 150px;"></div>
                                    <div class="signature-actions mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-signature="{{ $data['approval_role'] }}">Hapus</button>
                                    </div>
                                    <input type="hidden" name="signature_data" id="signature-data-{{ $data['approval_role'] }}">
                                    <button type="button" class="btn btn-sm btn-primary mt-2 save-signature"
                                            data-approval-role="{{ $data['approval_role'] }}"
                                            data-status="{{ $data['approval_status'] }}">
                                        Simpan Tanda Tangan
                                    </button>
                                </div>
                            @else
                                <div style="height: 80px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                    (Belum TTD)
                                </div>
                            @endif
                        </div>
                        <div style="margin-top: 5px;">
                            <div style="font-weight: 500; text-decoration: underline;">
                            @if($data['title'] === 'Diajukan Oleh' && auth()->check())
                                <div style="font-size: 0.8rem; color:rgb(11, 11, 11);">

                                </div>
                            @endif
                            {{ $bapp->{$data['name_field']} ?? '.........................' }}
                            </div>

                            <div style="font-size: 0.85rem; margin-top: 5px;">
                                {{ $data['title'] }}
                            </div>
                            <div style="font-size: 0.8rem; color: #6b7280;">
                                {{ $data['position'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($bapp->status !== 'Disetujui' &&
        ((in_array($bapp->status, ['Draft', 'Diajukan', 'Diperiksa', 'Selesai', 'Ditolak']) && isset($isApproval)) ||
        (!isset($isApproval) && in_array($bapp->status, ['Draft', 'Diajukan', 'Diperiksa', 'Selesai', 'Ditolak']))))

    @if(auth()->user()->hasRole('vendor') || auth()->user()->hasRole('Assistant Manager Plantation') || auth()->user()->hasRole('Manager Plantation') || auth()->user()->hasRole('admin'))
    <!-- INPUT Form Tanda Tangan -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Tanda Tangan Digital</h5>
        </div>
        <div class="card-body">
            <form id="signature-form" action="{{ route('bapp.save-signature', $bapp->kode_bapp) }}" method="POST">
                @csrf
                <div class="text-center">
                    @if(($bapp->status === 'Diajukan' && (auth()->user()->hasRole('vendor') || auth()->user()->hasRole('admin'))) ||
                        ($bapp->status === 'Diperiksa' && (auth()->user()->hasRole('manager-plantation') || auth()->user()->hasRole('admin'))))
                        <div class="mb-3 text-left">
                            <label for="complaint" class="form-label">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Keluhan/Komentar (Opsional)
                            </label>
                            <textarea name="complaint" id="complaint" class="form-control" rows="3"
                                placeholder="Masukkan keluhan atau komentar (jika ada)"></textarea>
                            <small class="text-muted">Isi kolom ini jika ada keluhan atau catatan khusus</small>
                        </div>
                    @endif

                    <div style="border: 1px solid #ddd; width: 100%; height: 150px; margin-bottom: 10px;">
                        <canvas id="signature-pad" style="width: 100%; height: 100%;"></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signature-data">
                    <input type="hidden" name="status" id="signature-status" value="Diajukan">
                    <input type="hidden" name="jenis" value="{{ $bapp instanceof \App\Models\BappAngkut ? 'angkut' : 'tebang' }}">
                    <input type="hidden" name="signature_role" id="signature-role" value="pengaju">

                    @if($bapp->status === 'Diajukan' && (auth()->user()->hasRole('vendor') || auth()->user()->hasRole('admin')))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> Anda akan menandatangani sebagai Pemeriksa (Vendor/Admin).
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('signature-role').value = 'pemeriksa';
                                document.getElementById('signature-status').value = 'Diperiksa';
                            });
                        </script>
                    @elseif($bapp->status === 'Diperiksa' && (auth()->user()->hasRole('manager-plantation') || auth()->user()->hasRole('admin')))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> Anda akan menandatangani sebagai Penyetuju (Manager Plantation/Admin).
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('signature-role').value = 'penyetuju';
                                document.getElementById('signature-status').value = 'Disetujui';
                            });
                        </script>
                    @elseif(($bapp->status === 'Draft' || $bapp->status === null) && (auth()->user()->hasRole('Assistant Manager Plantation') || auth()->user()->hasRole('admin')))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> Anda akan menandatangani sebagai Pengaju (Asst. Manager/Admin).
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('signature-role').value = 'pengaju';
                                document.getElementById('signature-status').value = 'Diajukan';
                            });
                        </script>
                    @endif

                    <button type="button" id="clear-signature" class="btn btn-sm btn-secondary">
                        <i class="fas fa-eraser"></i> Hapus
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan & Ajukan
                    </button>
                </div>
            </form>
        </div>
    @endif
    </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mt-4">
        <div>
            <a href="{{ route('bapp.index', ['jenis' => 'angkut']) }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('bapp.print', ['bapp' => $bapp->id, 'jenis' => $jenis]) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak BAPP
            </a>
        </div>

        @if($bapp->status_approval === 'draft' && !empty($bapp->ttd_dibuat_oleh) && (auth()->user()->hasRole('Assistant Manager Plantation') || auth()->user()->hasRole('admin')))
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitBappModal">
            <i class="fas fa-paper-plane me-1"></i> Ajukan ke Vendor
        </button>
        @endif
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '{{ session('error') }}',
        confirmButtonText: 'Tutup'
    });
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) return;

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)'
    });

    // Fungsi untuk menyesuaikan ukuran canvas
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }

    // Panggil saat pertama kali load dan saat window di-resize
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Tombol clear
    const clearButton = document.getElementById('clear-signature');
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            signaturePad.clear();
        });
    }

    // Handle form submission
    const form = document.getElementById('signature-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (signaturePad.isEmpty()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Harap beri tanda tangan terlebih dahulu!',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            const signatureInput = document.getElementById('signature-data');
            signatureInput.value = signaturePad.toDataURL('image/png');

            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

            try {
                // Create FormData object
                const formData = new FormData(form);
                // Add the signature data
                formData.append('signature', signatureInput.value);

                // Get complaint value if it exists and add to form data
                const complaintInput = document.getElementById('complaint');
                if (complaintInput && complaintInput.value) {
                    formData.append('complaint', complaintInput.value);
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        text: result.message || 'Tanda tangan berhasil disimpan',
                        confirmButtonColor: '#3085d6',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    throw new Error(result.message || 'Gagal menyimpan tanda tangan');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Terjadi kesalahan saat menyimpan tanda tangan',
                    confirmButtonColor: '#d33',
                });
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    }
});
</script>
@endpush

@endsection
