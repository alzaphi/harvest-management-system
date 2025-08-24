@extends('layouts.master')

@section('page-title', 'Persetujuan LKT - ' . $lkt->kode_lkt)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spt.css') }}?v=1.0.3">
<style>
    .signature-pad {
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
        max-width: 400px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .signature-buttons {
        margin-top: 10px;
        text-align: center;
    }
    .signature-box {
        text-align: center;
        padding: 1rem;
        width: 23%;
    }
    .signature-img {
        max-height: 80px;
        margin-bottom: 4px;
    }
    .signature-line {
        border-top: 1px solid #000;
        margin: 6px 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255,255,255)',
            penColor: 'rgb(0,0,0)'
        });

        document.getElementById('clear-signature')?.addEventListener('click', () => {
            signaturePad.clear();
        });

        document.getElementById('approval-form')?.addEventListener('submit', function(e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Harap beri tanda tangan terlebih dahulu.');
                return false;
            }
            document.getElementById('signature-data').value = signaturePad.toDataURL();
        });
    }

    document.getElementById('reject-btn')?.addEventListener('click', function() {
        $('#rejectModal').modal('show');
    });
});
</script>
@endpush

@section('content')
<div class="container-fluid px-1 py-2">
    <div class="spt-container" style="padding: 0.75rem 1rem;">
        <div class="spt-show-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo jbm.png') }}" alt="Logo" class="logo">
                <div class="company-info">
                    <h1 class="company-name">PT. JHONLIN BATU MANDIRI</h1>
                    <p class="company-subtitle">Harvest Management System</p>
                </div>
            </div>

            <div class="document-title">
                <h1>LEMBAR KERJA TEBANG</h1>
                <div class="document-code">{{ $lkt->kode_lkt }}</div>
                @php
                    $status = $lkt->status ?? 'Diajukan';
                    $statusClass = [
                        'Disetujui' => 'status-active',
                        'Diajukan' => 'status-pending',
                        'Ditolak' => 'status-draft'
                    ][$status] ?? 'status-draft';
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
            </div>
        </div>

        <table class="spt-show-table">
            <tr><td class="label">Tanggal Tebang</td><td>{{ \Carbon\Carbon::parse($lkt->tanggal_tebang)->format('d F Y') }}</td></tr>
            <tr><td class="label">Kode / Vendor Tebang</td><td>{{ $lkt->kode_vendor_tebang }} / {{ $lkt->vendorTebang->nama_vendor ?? '-' }}</td></tr>
            <tr><td class="label">Jenis Tebangan</td><td>{{ $lkt->jenis_tebangan ?? '-' }}</td></tr>
            <tr><td class="label">Kode / Vendor Angkut</td><td>{{ $lkt->kode_vendor_angkut ?? '-' }} / {{ $lkt->vendorAngkut->nama_vendor ?? '-' }}</td></tr>
            <tr><td class="label">Driver / Kode Lambung / Plat Nomor</td><td>{{ $lkt->driver->nama_vendor ?? '-' }} / {{ $lkt->driver->kode_lambung ?? '-' }} / {{ $lkt->driver->plat_nomor ?? '-' }}</td></tr>
            <tr><td class="label">Nomor SPT</td><td>{{ $lkt->kode_spt }}</td></tr>
            <tr><td class="label">Kode Petak / Status / Luas</td><td>{{ $lkt->kode_petak }} / {{ $lkt->petak->aktif ==1 ? 'Aktif' : 'Tidak Aktif' }} / {{ $lkt->petak->luas_area ?? '-' }} Ha</td></tr>
            <tr><td class="label">Tarif Zona Angkutan</td><td>Zona {{ $lkt->tarif_zona_angkutan ?? '-' }}</td></tr>
        </table>

        <div class="d-flex justify-content-between mt-3">
            @foreach([
                ['dibuat_oleh', 'ttd_dibuat_oleh_path', 'Mandor'],
                ['diperiksa_oleh', 'ttd_diperiksa_oleh_path', 'Pemeriksa 1'],
                ['disetujui_oleh', 'ttd_disetujui_oleh_path', 'Pemeriksa 2'],
                ['ditimbang_oleh', 'ttd_ditimbang_oleh_path', 'Petugas Timbangan']
            ] as [$user, $ttd, $role])
            <div class="signature-box">
                @if($lkt->$ttd)
                    <img src="{{ asset('storage/' . $lkt->$ttd) }}" alt="TTD" class="signature-img">
                @else
                    <div style="height: 80px; background: #f9f9f9; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center;">Belum TTD</div>
                @endif
                <div class="signature-line"></div>
                <div><strong>{{ $lkt->$user ?? '-' }}</strong></div>
                <div style="font-size: 0.85rem; color: #666;">{{ $role }}</div>
            </div>
            @endforeach
        </div>

        @if($lkt->status === 'Diajukan' || $lkt->status === 'Diperiksa')
            <!-- Form Persetujuan -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Persetujuan {{ $lkt->status === 'Diperiksa' ? 'Pemeriksa 2' : 'Pemeriksa 1' }}</h5>
                </div>
                <div class="card-body">
                    @if($lkt->status === 'Diperiksa' && $lkt->ttd_diperiksa_oleh_path)
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Dokumen ini telah disetujui oleh Pemeriksa 1 dan menunggu persetujuan Anda sebagai Pemeriksa 2.
                        </div>
                    @endif
                    
                    <form id="approval-form" action="{{ route('lkt.approval.approve', $lkt->id) }}" method="POST">
                        @csrf
                        <div class="form-group text-center">
                            <label for="signature-pad" class="form-label">Tanda Tangan Digital</label>
                            <canvas id="signature-pad" class="signature-pad" width="400" height="100"></canvas>
                            <input type="hidden" name="signature" id="signature-data">
                            <div class="signature-buttons">
                                <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">Hapus</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('lkt.approval.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                            </a>
                            <div>
                                <button type="button" id="reject-btn" class="btn btn-danger me-2">
                                    <i class="fas fa-times me-1"></i> Tolak
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> {{ $lkt->status === 'Diperiksa' ? 'Setujui' : 'Ajukan ke Pemeriksa 2' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($lkt->status === 'Disetujui')
            <div class="alert alert-success mt-4">
                <i class="fas fa-check-circle me-2"></i>
                Dokumen ini telah disetujui oleh kedua pemeriksa dan menunggu tanda tangan petugas timbangan.
            </div>
            
            <!-- Tanda Tangan Petugas Timbangan -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Tanda Tangan Petugas Timbangan</h5>
                </div>
                <div class="card-body">
                    @if($lkt->ttd_ditimbang_oleh_path)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Dokumen ini telah ditandatangani oleh petugas timbangan.
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $lkt->ttd_ditimbang_oleh_path) }}" alt="Tanda Tangan Petugas Timbangan" class="img-fluid" style="max-height: 100px;">
                            <p class="mt-2 mb-0">Ditandatangani oleh: {{ $lkt->ditimbang_oleh ?? 'Petugas Timbangan' }}</p>
                            <p class="text-muted">Pada: {{ $lkt->ttd_ditimbang_pada ? \Carbon\Carbon::parse($lkt->ttd_ditimbang_pada)->format('d/m/Y H:i') : '-' }}</p>
                        </div>
                    @else
                        <form id="timbangan-form" action="{{ route('lkt.approval.timbangan', $lkt->id) }}" method="POST">
                            @csrf
                            <div class="form-group text-center">
                                <label for="signature-pad-timbangan" class="form-label">Tanda Tangan Digital</label>
                                <div style="border: 1px solid #ddd; margin-bottom: 10px;">
                                    <canvas id="signature-pad-timbangan" class="signature-pad" width="400" height="200"></canvas>
                                </div>
                                <input type="hidden" name="signature" id="signature-data-timbangan">
                                <button type="button" id="clear-signature-timbangan" class="btn btn-sm btn-outline-secondary mb-3">
                                    <i class="fas fa-eraser me-1"></i> Hapus Tanda Tangan
                                </button>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('lkt.approval.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                                </a>
                                <button type="submit" class="btn btn-success" id="submit-timbangan">
                                    <i class="fas fa-signature me-1"></i> Simpan Tanda Tangan
                                </button>
                            </div>
                        </form>
                        
                        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const canvas = document.getElementById('signature-pad-timbangan');
                                const clearButton = document.getElementById('clear-signature-timbangan');
                                const form = document.getElementById('timbangan-form');
                                const signatureInput = document.getElementById('signature-data-timbangan');
                                
                                // Make canvas responsive
                                function resizeCanvas() {
                                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                    canvas.width = canvas.offsetWidth * ratio;
                                    canvas.height = canvas.offsetHeight * ratio;
                                    canvas.getContext('2d').scale(ratio, ratio);
                                    signaturePad.clear(); // Clear on resize to avoid artifacts
                                }
                                
                                // Initialize signature pad
                                const signaturePad = new SignaturePad(canvas, {
                                    backgroundColor: 'rgb(255, 255, 255)',
                                    penColor: 'rgb(0, 0, 0)'
                                });
                                
                                // Handle window resize
                                window.addEventListener('resize', resizeCanvas);
                                resizeCanvas();
                                
                                // Clear button
                                clearButton.addEventListener('click', function() {
                                    signaturePad.clear();
                                });
                                
                                // Form submission
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    if (signaturePad.isEmpty()) {
                                        alert('Harap beri tanda tangan terlebih dahulu');
                                        return false;
                                    }
                                    
                                    // Set the signature data to the hidden input
                                    signatureInput.value = signaturePad.toDataURL('image/png');
                                    
                                    // Submit the form
                                    this.submit();
                                    
                                    // Disable the submit button to prevent double submission
                                    document.getElementById('submit-timbangan').disabled = true;
                                    document.getElementById('submit-timbangan').innerHTML = 
                                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                                });
                            });
                        </script>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Penolakan -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lkt.approval.reject', $lkt->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Tolak LKT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="alasan_penolakan" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" name="alasan_penolakan" id="alasan_penolakan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
