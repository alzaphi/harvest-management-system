@php use App\Models\LKT; @endphp

@extends('layouts.master')

@section('page-title', 'Lembar Kerja Tebang (LKT)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spt.css') }}?v=1.0.3">
<style>
    .spt-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        width: 100%;
        margin: 0;
        background: white;
        padding: 2rem;
        min-height: 100vh;
    }
    
    @media (min-width: 1200px) {
        .spt-container {
            padding: 2rem 5%;
        }
    }
    
    .logo-container {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .logo {
        height: 80px;
        margin-right: 1.5rem;
        padding: 0.5rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .company-name {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .company-subtitle {
        margin: 0.25rem 0 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    .document-title {
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .document-title h1 {
        font-size: 1.75rem;
        margin: 0 0 0.5rem;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .document-code {
        font-size: 1.1rem;
        font-weight: 600;
        color: #7f8c8d;
        margin-bottom: 0.5rem;
    }
    
    .spt-show-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }
    
    .spt-show-table .label {
        width: 30%;
        padding: 12px 15px;
        background-color: #f8f9fa;
        font-weight: 600;
        border: 1px solid #dee2e6;
    }
    
    .spt-show-table td {
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    /* Approval Progress */
    .approval-progress {
        margin: 2rem 0;
        padding: 1.5rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
    }
    
    .progress {
        height: 30px;
        background-color: #e9ecef;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .progress-bar {
        background-color: #28a745;
        transition: width 0.6s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    
    .approval-progress .d-flex {
        margin-top: 1rem;
    }
    
    .approval-progress .d-flex > div {
        position: relative;
        text-align: center;
        padding: 0 10px;
    }
    
    .approval-progress .d-flex > div.completed {
        color: #28a745;
        font-weight: bold;
    }
    .approval-progress .d-flex > div.current {
        color: #007bff;
        font-weight: bold;
    }
    .approval-progress .d-flex > div i {
        font-size: 1.25rem;
        display: block;
        margin: 0 auto 0.25rem;
    }
    
    /* Signature Box */
    .signature-box {
        text-align: center;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        background: #fff;
        transition: all 0.3s ease;
        width: 30%;
    }
    
    .signature-box.current-signer {
        background-color: #e7f1ff;
        border: 1px solid #b8d4ff;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .signature-box img {
        max-width: 100%;
        height: auto;
        max-height: 100px;
        display: block;
        margin: 0 auto 1rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 8px;
    }
    
    .signature-placeholder {
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        margin: 0 auto 1rem;
        color: #6c757d;
        width: 100%;
    }
    
    .signature-line {
        border-top: 1px solid #000;
        margin: 10px 0;
        width: 100%;
    }
    
    .signer-name {
        font-weight: 600;
        margin: 0.5rem 0 0.25rem;
        font-size: 1rem;
    }
    
    .signer-title {
        color: #6c757d;
        font-size: 0.875rem;
        margin: 0;
    }
    
    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background-color: #e3f8f3;
        color: #0d8a6a;
        border: 1px solid #a8e6d1;
    }
    
    .status-pending {
        background-color: #fff3e0;
        color: #f57c00;
        border: 1px solid #ffcc80;
    }
    
    .status-draft {
        background-color: #e9ecef;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    
    .status-rejected {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ef9a9a;
    }
    
    .status-completed {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    // Global variables
    let signaturePad = null;
    let currentForm = null;
    let resizeTimeout = null;
    
    // Initialize Signature Pad
    function initSignaturePad(canvasId = 'signatureCanvas') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        // Clear any existing signature pad
        if (signaturePad) {
            signaturePad.off();
        }
        
        // Set canvas size
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            
            if (signaturePad) {
                signaturePad.clear();
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                resizeCanvas();
            }, 100);
        });
        
        // Initialize signature pad
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        // Initial resize
        resizeCanvas();
    }
    
    // Clear signature
    function clearSignature(canvasId = 'signatureCanvas') {
        if (signaturePad) {
            signaturePad.clear();
        } else {
            initSignaturePad(canvasId);
        }
    }
    
    // Submit signature
    function submitSignature(status) {
        if (!signaturePad || signaturePad.isEmpty()) {
            alert('Harap beri tanda tangan terlebih dahulu');
            return false;
        }
        
        const signatureData = signaturePad.toDataURL();
        const catatan = document.getElementById('catatan').value;
        const form = document.getElementById('signatureForm');
        
        // Only check for timbangan data if this is the timbangan stage (P3) and user is admin/PT PAG
        const isTimbanganStage = {{ $lkt->approval_stage === \App\Models\LKT::STAGE_P3 ? 'true' : 'false' }};
        const isAdminOrPTPAG = {{ auth()->user()->hasRole(['admin', 'PT PAG']) ? 'true' : 'false' }};
        if (isTimbanganStage && isAdminOrPTPAG) {
            // Show loading
            Swal.fire({
                title: 'Memeriksa data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Check if LKT exists in hasil_tebang
            fetch(`/lkt/check-timbangan/{{ $lkt->id }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // If exists, submit the form
                        document.getElementById('signatureData').value = signatureData;
                        document.getElementById('status').value = status;
                        if (catatan) {
                            document.getElementById('catatan').value = catatan;
                        }
                        form.submit();
                    } else {
                        // If not exists, show error
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak dapat menandatangani LKT karena nomor LKT {{ $lkt->kode_lkt }} belum ditambahkan di Hasil Tebangan.',
                            confirmButtonText: 'Mengerti',
                            allowOutsideClick: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memeriksa data timbangan.',
                        confirmButtonText: 'Mengerti'
                    });
                });
        } else {
            // For other statuses, just submit the form
            document.getElementById('signatureData').value = signatureData;
            document.getElementById('status').value = status;
            if (catatan) {
                document.getElementById('catatan').value = catatan;
            }
            form.submit();
        }
        
        return true;
    }
    
    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        initSignaturePad();
        
        // Handle modal events
        const modal = document.getElementById('signatureModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function () {
                initSignaturePad();
            });
        }
        
    });
</script>
@endpush

@section('content')
<div class="container-fluid p-0">
    <div class="spt-container">
        <div class="spt-show-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo jbm.png') }}" alt="JBM Logo" class="logo">
                <div class="company-info">
                    <h1 class="company-name">PT. JHONLIN BATU MANDIRI</h1>
                    <p class="company-subtitle">Harvest Management System</p>
                </div>
                
            </div>

            <div class="document-title">
                
                <div class="document-code">{{ $lkt->kode_lkt }}
                    
                </div>
                @php
                    $status = $lkt->status ?? 'Draft';
                    $statusClass = [
                        'Disetujui' => 'status-active',
                        'Diajukan' => 'status-pending',
                        'Draft' => 'status-draft',
                        'Ditolak' => 'status-rejected',
                        'Selesai' => 'status-completed'
                    ][$status] ?? 'status-draft';
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                
            </div>
            
        </div>
        <div>
            <h2 style="text-align: center; color: black;">LEMBAR KERJA TEBANG</h2>
        </div>
        <table class="spt-show-table">
            <tr>
                <td class="label">Tanggal Tebang</td>
                <td>{{ $lkt->tanggal_tebang ? (is_string($lkt->tanggal_tebang) ? \Carbon\Carbon::parse($lkt->tanggal_tebang)->format('d F Y') : $lkt->tanggal_tebang->format('d F Y')) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kode / Vendor Tebang</td>
                <td>{{ $lkt->kode_vendor_tebang ?? '-' }} / {{ $lkt->vendorTebang->nama_vendor ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Tebangan</td>
                <td>{{ $lkt->jenis_tebangan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kode / Vendor Angkut</td>
                <td>{{ $lkt->kode_vendor_angkut ?? '-' }} / {{ $lkt->vendorAngkut->nama_vendor ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Driver / Kode Lambung / Plat Nomor</td>
                <td>
                    {{ $lkt->driver->nama_vendor ?? '-' }} /
                    {{ $lkt->driver->kode_lambung ?? '-' }} /
                    {{ $lkt->driver->plat_nomor ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Nomor SPT</td>
                <td>{{ $lkt->kode_spt }}</td>
            </tr>
            <tr>
                <td class="label">Kode Petak / Status / Luas</td>
                <td>
                    {{ $lkt->petak->kode_petak ?? '-' }} /
                    {{ $lkt->petak->aktif == 1 ? 'Aktif' : 'Tidak Aktif' }} /
                    {{ $lkt->petak->luas_area ?? '-' }} Ha
                </td>
            </tr>
            <tr>
                <td class="label">Tarif Zona Angkutan</td>
                <td>Zona {{ $lkt->tarif_zona_angkutan ?? '-' }}</td>
            </tr>
        </table>
        
        
        
        
        
        
        
        
        <!-- Approval Progress -->
        <div class="approval-progress">
            <h5 class="mb-3">Proses Persetujuan</h5>
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar bg-{{ $lkt->status === 'Ditolak' ? 'danger' : 'success' }}" 
                     role="progressbar" 
                     style="width: {{ $lkt->approval_progress }}%" 
                     aria-valuenow="{{ $lkt->approval_progress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ $lkt->approval_progress }}%
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="text-center {{ $lkt->approval_stage >= LKT::STAGE_DRAFT ? 'completed' : '' }}">
                    <div class="mb-1">Draft</div>
                    @if($lkt->approval_stage > LKT::STAGE_DRAFT)
                        <i class="fas fa-check-circle text-success"></i>
                    @else
                        <i class="far fa-circle"></i>
                    @endif
                </div>
                <div class="text-center {{ $lkt->approval_stage >= LKT::STAGE_P1 ? 'completed' : '' }}">
                    <div class="mb-1">Pemeriksaan 1</div>
                    @if($lkt->approval_stage > LKT::STAGE_P1)
                        <i class="fas fa-check-circle text-success"></i>
                    @else
                        <i class="far fa-circle"></i>
                    @endif
                </div>
                <div class="text-center {{ $lkt->approval_stage >= LKT::STAGE_P2 ? 'completed' : '' }}">
                    <div class="mb-1">Pemeriksaan 2</div>
                    @if($lkt->approval_stage > LKT::STAGE_P2)
                        <i class="fas fa-check-circle text-success"></i>
                    @else
                        <i class="far fa-circle"></i>
                    @endif
                </div>
                <div class="text-center {{ $lkt->approval_stage >= LKT::STAGE_P3 ? 'completed' : '' }}">
                    <div class="mb-1">Penimbangan</div>
                    @if($lkt->approval_stage >= LKT::STAGE_COMPLETED)
                        <i class="fas fa-check-circle text-success"></i>
                    @else
                        <i class="far fa-circle"></i>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Signatures -->
        <div style="display: flex; justify-content: space-between; margin: 1.5rem 0;">
            <!-- Pembuat -->
            <div class="signature-box {{ $lkt->approval_stage === LKT::STAGE_DRAFT ? 'current-signer' : '' }}">
                @php
                    $path = $lkt->ttd_dibuat_oleh_path ? str_replace('storage/app/public/', '', $lkt->ttd_dibuat_oleh_path) : null;
                    $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
                @endphp
                @if($path && file_exists($fullPath))
                    <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                         alt="Tanda Tangan"
                         style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto 1rem;">
                @else
                    <div class="signature-placeholder">Tanda Tangan</div>
                @endif
                <div class="signature-line"></div>
                <div class="signer-name">{{ $lkt->dibuat_oleh ?? 'Belum ada' }}</div>
                <div class="signer-title">Mandor</div>
                @if($lkt->ttd_dibuat_pada)
                    <div class="text-muted small mt-1">{{ is_string($lkt->ttd_dibuat_pada) ? \Carbon\Carbon::parse($lkt->ttd_dibuat_pada)->format('d/m/Y H:i') : $lkt->ttd_dibuat_pada->format('d/m/Y H:i') }}</div>
                @endif
            </div>

            <!-- Pemeriksa -->
            <div class="signature-box {{ $lkt->approval_stage === LKT::STAGE_P1 ? 'current-signer' : '' }}">
                @php
                    $path = $lkt->ttd_diperiksa_oleh_path ? str_replace('storage/app/public/', '', $lkt->ttd_diperiksa_oleh_path) : null;
                    $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
                @endphp
                @if($path && file_exists($fullPath))
                    <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                         alt="Tanda Tangan"
                         style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto 1rem;">
                @else
                    <div class="signature-placeholder">Tanda Tangan</div>
                @endif
                <div class="signature-line"></div>
                <div class="signer-name">{{ $lkt->diperiksa_oleh ?? 'Belum ada' }}</div>
                <div class="signer-title">Asisten Divisi Plantation</div>
                @if($lkt->ttd_diperiksa_pada)
                    <div class="text-muted small mt-1">{{ is_string($lkt->ttd_diperiksa_pada) ? \Carbon\Carbon::parse($lkt->ttd_diperiksa_pada)->format('d/m/Y H:i') : $lkt->ttd_diperiksa_pada->format('d/m/Y H:i') }}</div>
                @endif
            </div>

            <!-- Penyetuju -->
            <div class="signature-box {{ $lkt->approval_stage === LKT::STAGE_P2 ? 'current-signer' : '' }}">
                @php
                    $path = $lkt->ttd_disetujui_oleh_path ? str_replace('storage/app/public/', '', $lkt->ttd_disetujui_oleh_path) : null;
                    $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
                @endphp
                @if($path && file_exists($fullPath))
                    <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                         alt="Tanda Tangan"
                         style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto 1rem;">
                @else
                    <div class="signature-placeholder">Tanda Tangan</div>
                @endif
                <div class="signature-line"></div>
                <div class="signer-name">{{ $lkt->disetujui_oleh ?? 'Belum ada' }}</div>
                <div class="signer-title">Asisten Manager Plantation</div>
                @if($lkt->ttd_disetujui_pada)
                    <div class="text-muted small mt-1">{{ is_string($lkt->ttd_disetujui_pada) ? \Carbon\Carbon::parse($lkt->ttd_disetujui_pada)->format('d/m/Y H:i') : $lkt->ttd_disetujui_pada->format('d/m/Y H:i') }}</div>
                @endif
            </div>

            <!-- Penimbang -->
            <div class="signature-box {{ $lkt->approval_stage === LKT::STAGE_P3 ? 'current-signer' : '' }}">
                @php
                    $path = $lkt->ttd_ditimbang_oleh_path ? str_replace('storage/app/public/', '', $lkt->ttd_ditimbang_oleh_path) : null;
                    $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
                @endphp
                @if($path && file_exists($fullPath))
                    <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                         alt="Tanda Tangan"
                         style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto 1rem;">
                @else
                    <div class="signature-placeholder">Tanda Tangan</div>
                @endif
                <div class="signature-line"></div>
                <div class="signer-name">{{ $lkt->ditimbang_oleh ?? 'Belum ada' }}</div>
                <div class="signer-title">Staff timbangan PT PAG</div>
                @if($lkt->ttd_ditimbang_pada)
                    <div class="text-muted small mt-1">{{ is_string($lkt->ttd_ditimbang_pada) ? \Carbon\Carbon::parse($lkt->ttd_ditimbang_pada)->format('d/m/Y H:i') : $lkt->ttd_ditimbang_pada->format('d/m/Y H:i') }}</div>
                @endif
            </div>
        </div>
        
        <div class="action-buttons">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('lkt.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                
                @can('update', $lkt)
                    @if($lkt->status === 'Draft' || $lkt->status === 'Ditolak')
                        <a href="{{ route('lkt.edit', $lkt->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    @endif
                @endcan
                
                @if($lkt->canBeApprovedBy(auth()->user()))
                    @if($lkt->status !== 'Ditolak' && $lkt->status !== 'Selesai')
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signatureModal">
                                <i class="fas fa-signature me-1"></i> Tanda Tangan
                            </button>
                            <a href="{{ route('lkt.download-pdf', $lkt->id) }}" class="btn btn-info text-white" target="_blank">
                                <i class="fas fa-print me-1"></i> Cetak
                            </a>
                        </div>
                    @else
                        <a href="{{ route('lkt.download-pdf', $lkt->id) }}" class="btn btn-info text-white" target="_blank">
                            <i class="fas fa-print me-1"></i> Cetak
                        </a>
                    @endif
                @else
                    <a href="{{ route('lkt.download-pdf', $lkt->id) }}" class="btn btn-info text-white" target="_blank">
                        <i class="fas fa-print me-1"></i> Cetak
                    </a>
                @endcan
                
                @can('delete', $lkt)
                    @if($lkt->status === 'Draft' || $lkt->status === 'Ditolak')
                        <form action="{{ route('lkt.destroy', $lkt->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus LKT ini?')">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Signature Modal -->
        <div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="signatureModalLabel">Tanda Tangan Digital</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="signatureForm" action="{{ route('lkt.update-status', $lkt->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="signature" id="signatureData">
                        <input type="hidden" name="status" id="status">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Masukkan catatan jika diperlukan"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanda Tangan</label>
                                <div class="border rounded p-2">
                                    <canvas id="signatureCanvas" style="width: 100%; height: 200px; touch-action: none;"></canvas>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                        <i class="fas fa-eraser me-1"></i> Hapus
                                    </button>
                                    <small class="text-muted">Tanda tangan di sini</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="submitSignature('{{ $lkt->status === 'Draft' ? 'Diajukan' : ($lkt->status === 'Diperiksa' ? 'Disetujui' : 'Selesai') }}')">
                                <i class="fas fa-check-circle me-1"></i> Simpan & Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
