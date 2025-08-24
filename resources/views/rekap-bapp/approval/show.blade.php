@extends('layouts.master')

@section('page-title', 'Persetujuan SPD - ' . $spd->no_spd)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spt.css') }}?v=1.0.3">
<style>
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
        width: 20%; 
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
    .document-title {
        text-align: center;
        margin: 20px 0;
    }
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-draft { background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
    .status-diajukan { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .status-diperiksa { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .status-diverifikasi { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .status-disetujui { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-selesai { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-dibayar { background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
    .status-ditolak { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info-grid { margin: 20px 0; }
    .info-row { display: flex; margin-bottom: 8px; }
    .info-label { width: 250px; font-weight: bold; }
    .bordered-input {
        border: 1px solid #000;
        padding: 2px 5px;
        display: inline-block;
        min-width: 300px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi signature pad
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
    });

    // Fungsi untuk mengatur ulang ukuran canvas
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = 200 * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }

    // Atur ulang ukuran canvas saat halaman dimuat dan di-resize
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Tombol hapus tanda tangan
    document.getElementById('clear-signature')?.addEventListener('click', function() {
        signaturePad.clear();
    });

    // Validasi form approval
    const approvalForm = document.getElementById('approval-form');
    if (approvalForm) {
        approvalForm.addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Tanda Tangan Kosong',
                    text: 'Silakan beri tanda tangan terlebih dahulu',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#3085d6',
                });
                return false;
            }

            // Simpan tanda tangan ke input tersembunyi
            document.getElementById('signature-data').value = signaturePad.toDataURL('image/png');
            
            // Submit form tanpa menampilkan loading
            return true;
        });
    }

    // Validasi form penolakan
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const alasan = document.getElementById('alasan_penolakan').value.trim();
            if (!alasan) {
                e.preventDefault();
                document.getElementById('alasan_penolakan').classList.add('is-invalid');
                return false;
            }
            
            // Tampilkan konfirmasi
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Penolakan',
                text: 'Apakah Anda yakin ingin menolak SPD ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menyimpan penolakan',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            // Submit form setelah dialog loading muncul
                            rejectForm.submit();
                        }
                    });
                }
            });
            
            return false;
        });
    }
    
    // Hilangkan pesan error saat user mulai mengetik
    document.getElementById('alasan_penolakan')?.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
@endpush

@section('content')
<div class="container-fluid px-1 py-2">
    <div class="spt-container" style="padding: 0.75rem 1rem;">
        <!-- HEADER -->
        <div class="spt-show-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo jbm.png') }}" alt="JBM Logo" class="logo">
                <div class="company-info">
                    <h1 class="company-name">PT. JHONLIN BATU MANDIRI</h1>
                    <p class="company-subtitle">Harvest Management System</p>
                </div>
            </div>

            <div class="document-title">
                <h1>SURAT PERMINTAAN DANA</h1>
                <div style="display: flex; justify-content: center; gap: 20px; margin: 10px 0;">
                    <div>Nomor: <strong>{{ $spd->no_spd ?? $documentNumber ?? '' }}</strong></div>
                    <div>Tanggal: <strong>{{ $spd->tanggal_spd ? \Carbon\Carbon::parse($spd->tanggal_spd)->format('d/m/Y') : ($tanggalPengajuan ?? now()->format('d/m/Y')) }}</strong></div>
                </div>
                @php
                    $user = auth()->user();
                    $showApproveButton = false;
                    $showRejectButton = false;
                    $showVerifyButton = false;
                    $showPaymentButton = false;
                    
                    // Check permissions based on status and user role
                    if ($user->hasRole('Admin')) {
                        if ($spd->status === 'Diajukan') {
                            $showVerifyButton = true;
                            $showRejectButton = true;
                        } elseif ($spd->status === 'Diverifikasi') {
                            $showApproveButton = true;
                            $showRejectButton = true;
                        }
                    } elseif ($user->hasRole('Finance')) {
                        if ($spd->status === 'Disetujui') {
                            $showPaymentButton = true;
                        }
                    }
                    
                    // Get status class
                    $statusClass = [
                        'Draft' => 'status-draft',
                        'Diajukan' => 'status-diajukan',
                        'Diperiksa' => 'status-diperiksa',
                        'Diverifikasi' => 'status-diverifikasi',
                        'Disetujui' => 'status-disetujui',
                        'Selesai' => 'status-selesai',
                        'Ditolak' => 'status-ditolak'
                    ];
                @endphp
                <span class="status-badge {{ $statusClass[$spd->status] ?? '' }}">
                    @if($spd->status === 'Diajukan')
                        <i class="fas fa-clock me-1"></i> Menunggu Diperiksa
                    @elseif($spd->status === 'Diperiksa')
                        <i class="fas fa-user-check me-1"></i> Menunggu Verifikasi
                    @elseif($spd->status === 'Diverifikasi')
                        <i class="fas fa-check-double me-1"></i> Menunggu Persetujuan
                    @elseif($spd->status === 'Disetujui')
                        <i class="fas fa-file-invoice-dollar me-1"></i> Menunggu Pembayaran
                    @elseif($spd->status === 'Selesai')
                        <i class="fas fa-check-circle me-1"></i> Selesai
                    @elseif($spd->status === 'Ditolak')
                        <i class="fas fa-times-circle me-1"></i> Ditolak
                    @else
                        {{ $spd->status }}
                    @endif
                </span>
            </div>
        </div>

        <!-- INFO VERTICAL -->
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Kepada</div>
                <div>: Bagian Keuangan</div>
            </div>
            <div class="info-row">
                <div class="info-label">Permintaan Dana Untuk</div>
                <div>: Pembayaran Tebang, Muat dan Angkut Tahun {{ date('Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Periode Ke</div>
                <div>: <strong>{{ $spd->periode ?? $periodeBapp ?? '' }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Sejumlah</div>
                <div>: <span class="bordered-input">Rp{{ number_format($spd->total_dana ?? $grandTotalPendapatan ?? 0, 0, ',', '.') }}</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">Terbilang</div>
                <div>: <span class="bordered-input">{{ $terbilang ?? '' }}</span></div>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="d-flex justify-content-between mt-4">
            @foreach([
                ['Diajukan Oleh', 'ttd_diajukan_oleh', 'Mgr. Plantation'],
                ['Diverifikasi Oleh', 'ttd_diverifikasi_oleh', 'Ast. Mgr. QA On Farm'],
                ['Diketahui Oleh', 'ttd_diketahui_oleh', 'Mgr. CDR & Agronomi'],
                ['Disetujui Oleh', 'ttd_disetujui_oleh', 'Direktur'],
                ['Dibayar Oleh', 'ttd_dibayar_oleh', 'GM FAT']
            ] as [$role, $signature, $person])
                @php
                    // Check if this is the current step
                    $isCurrentStep = false;
                    if ($spd->status === 'Diajukan' && $role === 'Diverifikasi Oleh') {
                        $isCurrentStep = true;
                    } elseif ($spd->status === 'Diperiksa' && $role === 'Diketahui Oleh') {
                        $isCurrentStep = true;
                    } elseif ($spd->status === 'Diverifikasi' && $role === 'Disetujui Oleh') {
                        $isCurrentStep = true;
                    } elseif ($spd->status === 'Disetujui' && $role === 'Dibayar Oleh') {
                        $isCurrentStep = true;
                    }
                    
                    // Get user relationship
                    $relationship = strtolower(str_replace(' ', '_', explode(' ', $role)[0]));
                    $user = $spd->{$relationship};
                    $hasSignature = !empty($spd->$signature);
                @endphp
                <div class="signature-box {{ $isCurrentStep ? 'current-step' : '' }}">
                    @if ($hasSignature)
                        <img src="{{ asset('storage/' . $spd->$signature) }}" alt="Tanda Tangan {{ $person }}" style="max-width: 100%; max-height: 100px;">
                    @else
                        <div style="height: 100px; width: 100%; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 0.8rem;">
                            @if($isCurrentStep)
                                Tanda Tangan
                            @endif
                        </div>
                    @endif
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        @if($user)
                            {{ $user->name }}
                        @elseif($hasSignature)
                            {{ auth()->user()->name }}
                        @endif
                    </div>
                    <div class="signature-role">{{ $person }}</div>
                    @php
                        $dateField = strtolower(str_replace(' ', '_', $role)) . '_pada';
                    @endphp
                    @if(!empty($spd->$dateField))
                        <div class="signature-date" style="font-size: 0.75rem; color: #888;">
                            {{ \Carbon\Carbon::parse($spd->$dateField)->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @can('approve-dana')
        <!-- APPROVAL FORM -->
        @if(in_array($spd->status, ['Diajukan', 'Diperiksa', 'Diverifikasi', 'Disetujui']))
            @php
                $nextStep = '';
                $nextStatus = '';
                $buttonText = '';
                $message = '';
                
                switch($spd->status) {
                    case 'Diajukan':
                    case 'Menunggu Diperiksa':
                        $nextStep = 'Diperiksa';
                        $buttonText = 'Ajukan untuk Verifikasi';
                        $nextStatus = 'Diperiksa';
                        $message = 'Dokumen ini telah diajukan dan menunggu verifikasi Anda sebagai Ast. Mgr. QA On Farm';
                        break;
                    case 'Diperiksa':
                        $nextStep = 'Diverifikasi';
                        $buttonText = 'Ajukan untuk Persetujuan';
                        $nextStatus = 'Diverifikasi';
                        $message = 'Dokumen ini telah diperiksa dan menunggu verifikasi Anda sebagai Mgr. CDR & Agronomi';
                        break;
                    case 'Diverifikasi':
                        $nextStep = 'Disetujui';
                        $buttonText = 'Setujui';
                        $nextStatus = 'Disetujui';
                        $message = 'Dokumen ini telah diverifikasi dan menunggu persetujuan Anda sebagai Direktur';
                        break;
                    case 'Disetujui':
                        $nextStep = 'Dibayar';
                        $buttonText = 'Konfirmasi Pembayaran';
                        $nextStatus = 'Selesai';
                        $message = 'Dokumen ini telah disetujui dan menunggu konfirmasi pembayaran oleh GM FAT';
                        break;
                }
            @endphp
            
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i> {{ $nextStep === 'Dibayar' ? 'Konfirmasi Pembayaran' : 'Persetujuan ' . $nextStep }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(!empty($message))
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ $message }}
                    </div>
                    @endif
                    
                    <form id="approval-form" action="{{ route('spd.approval.process', $spd->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        
                        @if(auth()->user()->can('approve-dana'))
                        <div class="form-group text-center mb-4">
                            <label for="signature-pad" class="form-label">Tanda Tangan Digital</label>
                            <div style="border: 1px solid #ddd; margin-bottom: 10px;">
                                <canvas id="signature-pad" class="signature-pad" width="400" height="200"></canvas>
                            </div>
                            <input type="hidden" name="signature_data" id="signature-data">
                            <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary mb-3">
                                <i class="fas fa-eraser me-1"></i> Hapus Tanda Tangan
                            </button>
                        </div>
                        @endif                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('spd.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                                </a>
                                <a href="{{ route('spd.approval.detail', $spd->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i> Lihat Detail
                                </a>
                            </div>
                            <div>
                                @if($spd->status !== 'Disetujui' && $spd->status !== 'Selesai' || auth()->user()->hasRole('admin'))
                                    <button type="button" id="reject-btn" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="fas fa-times me-1"></i> Tolak
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i> {{ $buttonText }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($spd->status === 'Selesai')
            <div class="alert alert-success mt-4">
                <i class="fas fa-check-circle me-1"></i>
                Dokumen ini telah selesai diproses dan pembayaran telah dikonfirmasi.
            </div>
        @elseif($spd->status === 'Ditolak')
            <div class="alert alert-danger mt-4">
                <i class="fas fa-times-circle me-1"></i>
                Dokumen ini telah ditolak.
                @if($spd->alasan_penolakan)
                    <div class="mt-2">
                        <strong>Alasan Penolakan:</strong>
                        <p class="mb-0">{{ $spd->alasan_penolakan }}</p>
                    </div>
                @endif
            </div>
        @endif
        @endcan

        <!-- Modal Penolakan -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('spd.approval.reject', $spd->id) }}" method="POST" id="rejectForm">
                        @csrf
                        @method('POST')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="rejectModalLabel">Tolak SPD</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="alasan_penolakan" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alasan_penolakan" name="alasan_penolakan" rows="4" required placeholder="Masukkan alasan penolakan"></textarea>
                                <div class="invalid-feedback">Harap isi alasan penolakan</div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Pastikan alasan penolakan sudah jelas dan dapat dipahami oleh pemohon.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> Tolak SPD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection