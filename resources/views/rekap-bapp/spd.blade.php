@extends('layouts.master')

@section('page-title', 'Surat Permintaan Dana (SPD)')

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
    .signature-buttons { margin-top: 10px; text-align: center; }
    .document-title { text-align: center; margin: 20px 0; }
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-draft { background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
    .status-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-active { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .info-grid { margin: 20px 0; }
    .info-row { display: flex; margin-bottom: 8px; }
    .info-label { width: 250px; font-weight: bold; }
    .bordered-input {
        border: 1px solid #000;
        padding: 2px 5px;
        display: inline-block;
        min-width: 300px;
    }
    .signature-box { text-align: center; width: 20%; }
    .signature-line { width: 100%; height: 1px; background-color: #000; margin: 5px 0; }
    .signature-role { font-size: 12px; color: #6c757d; }
    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.15);
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
    .btn-primary {
        padding: 0.5rem 1.25rem;
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
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = 150 * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        document.getElementById('clear-signature')?.addEventListener('click', () => signaturePad.clear());

        const form = document.getElementById('signature-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Harap beri tanda tangan terlebih dahulu');
                    return false;
                }
                document.getElementById('signature-data').value = signaturePad.toDataURL('image/png');
            });
        }
    }
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const submitSpdModal = document.getElementById('submitSpdModal');
    let modalInstance = null;
    
    if (submitSpdModal) {
        modalInstance = new bootstrap.Modal(submitSpdModal);
    }
    
    // Handle form submission for signature
    const signatureForms = document.querySelectorAll('.signature-form');
    
    signatureForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
            
            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menyimpan tanda tangan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Terjadi kesalahan saat menyimpan tanda tangan',
                    confirmButtonText: 'Tutup'
                });
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });

    // Handle SPD submission form
    const submitSpdForm = document.getElementById('submitSpdForm');
    if (submitSpdForm) {
        submitSpdForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengajukan...';
            
            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close the modal
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Show success message and redirect
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'SPD berhasil diajukan',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Redirect to approval page
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat mengajukan SPD');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Terjadi kesalahan saat mengajukan SPD',
                    confirmButtonText: 'Tutup'
                });
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
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
                    $status = $spd->status ?? 'Draft';
                    $statusClass = [
                        'Disetujui' => 'status-active',
                        'Diajukan' => 'status-pending',
                        'Draft' => 'status-draft'
                    ][$status] ?? 'status-draft';
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
            </div>
        </div>

        <!-- INFO VERTICAL -->
        <div class="info-grid">
            <div class="info-row"><div class="info-label">Kepada</div><div>: Bagian Keuangan</div></div>
            <div class="info-row"><div class="info-label">Permintaan Dana Untuk</div><div>: Pembayaran Tebang, Muat dan Angkut Tahun {{ date('Y') }}</div></div>
            <div class="info-row"><div class="info-label">Periode Ke</div><div>: <strong>{{ $spd->periode ?? $periodeBapp ?? '' }}</strong></div></div>
            <div class="info-row">
                <div class="info-label">Sejumlah</div>
                <div>: <span class="bordered-input">Rp {{ number_format($spd->total_dana ?? $grandTotalPendapatan ?? 0, 0, ',', '.') }}</span></div>
            </div>
            <div class="info-row"><div class="info-label">Terbilang</div><div>: <span class="bordered-input">{{ $terbilang ?? '' }}</span></div></div>
        </div>

        <!-- SIGNATURE BOXES -->
        <div style="display: flex; justify-content: space-between; margin: 2rem 0;">
            @foreach([
                ['Diajukan Oleh', 'ttd_diajukan_oleh', 'Mgr. Plantation'],
                ['Diverifikasi Oleh', 'ttd_diverifikasi_oleh', 'Ast. Mgr. QA On Farm'],
                ['Diketahui Oleh', 'ttd_diketahui_oleh', 'Mgr. CDR & Agronomi'],
                ['Disetujui Oleh', 'ttd_disetujui_oleh', 'Direktur'],
                ['Dibayar Oleh', 'ttd_dibayar_oleh', 'GM FAT'],
            ] as [$role, $signature, $person])
                <div class="signature-box">
                    @if ($spd->$signature)
                        <img src="{{ asset('storage/' . $spd->$signature) }}" alt="Tanda Tangan {{ $person }}" style="max-width: 100%; max-height: 100px;">
                    @else
                        <div style="height: 100px; width: 100%; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 0.8rem;">
                            Tanda Tangan
                        </div>
                    @endif
                    <div class="signature-line"></div>
                    <div class="signature-name">
                        @php
                            $relationship = match($role) {
                                'Diajukan Oleh' => 'diajukanOleh',
                                'Diverifikasi Oleh' => 'diverifikasiOleh',
                                'Diketahui Oleh' => 'diketahuiOleh',
                                'Disetujui Oleh' => 'disetujuiOleh',
                                'Dibayar Oleh' => 'dibayarOleh',
                                default => null
                            };
                            $user = $relationship ? $spd->$relationship : null;
                            $hasSignature = !empty($spd->{'ttd_' . strtolower(str_replace(' ', '_', $role))});
                        @endphp
                        @if($user)
                            {{ $user->name }}
                        @elseif($hasSignature)
                            {{ auth()->user()->name }}
                        @endif
                    </div>
                    <div class="signature-role">{{ $person }}</div>
                </div>
            @endforeach
        </div>

        <!-- Tahap tanda tangan atau ajukan -->
        @if(empty($spd->ttd_diajukan_oleh))
            <!-- Form tanda tangan -->
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">Tanda Tangan Digital</h5></div>
                <div class="card-body">
                    <form id="signature-form" class="signature-form" action="{{ route('spd.sign', $spd->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="signature_type" value="diajukan_oleh">
                        <input type="hidden" name="signature_data" id="signature-data">
                        <canvas id="signature-pad" class="signature-pad"></canvas>
                        <div class="signature-buttons">
                            <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">Hapus</button>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Simpan Tanda Tangan</button>
                    </form>
                </div>
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

        <!-- Tombol kembali & cetak -->
        <div class="footer-actions mt-4">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('bapp.recap.detail', ['period' => $spd->periode]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Cetak SPD
                    </button>
                </div>
                @if($spd->status === 'Draft' && !empty($spd->ttd_diajukan_oleh))
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitSpdModal">
                        <i class="fas fa-paper-plane me-1"></i> Ajukan untuk Diperiksa
                    </button>
                @endif
            </div>
        </div>

        <!-- Modal Konfirmasi Pengajuan -->
        <div class="modal fade" id="submitSpdModal" tabindex="-1" aria-labelledby="submitSpdModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="submitSpdModalLabel">
                            <i class="fas fa-paper-plane me-2"></i> Konfirmasi Pengajuan SPD
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="bg-soft-warning p-3 rounded-circle d-inline-block mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                            <h5 class="mb-3">Anda yakin ingin mengajukan SPD ini?</h5>
                            <p class="text-muted">Pastikan data SPD sudah benar sebelum diajukan. Setelah diajukan, status akan berubah menjadi <span class="badge bg-primary">Diajukan</span> dan akan masuk ke daftar persetujuan.</p>
                        </div>
                        
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">No. SPD</span>
                                    <span class="fw-bold">{{ $spd->no_spd ?? 'Belum ada nomor' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Periode</span>
                                    <span class="fw-bold">{{ $spd->periode }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Dana</span>
                                    <span class="fw-bold">Rp {{ number_format($spd->total_dana, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle mt-1 me-2"></i>
                                <div>
                                    <small class="d-block">Pastikan tanda tangan "Diajukan Oleh" sudah diisi.</small>
                                    <small class="d-block">Status tidak dapat diubah kembali ke "Draft" setelah diajukan.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Batal
                        </button>
                        <form id="submitSpdForm" action="{{ route('spd.submit-review', $spd->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Ya, Ajukan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
