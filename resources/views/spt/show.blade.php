@extends('layouts.master')

@section('page-title', 'Surat Perintah Tebang (SPT)')

@php
    $header = 'Surat Perintah Tebang (SPT)';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Surat Perintah Tebang (SPT)', 'url' => route('spt.index')],
        ['title' => 'Detail ' . $spt->kode_spt]
    ];
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('css/spt.css') }}?v=1.0.1">
<style>
    .approval-progress {
        margin: 2rem 0;
        padding: 1rem;
        background: #f8f9fc;
        border-radius: 0.5rem;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 0.25rem;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    .approval-progress .d-flex > div {
        position: relative;
        color: #6c757d;
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
    .signature-box {
        text-align: center;
        padding: 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    .current-signer {
        background-color: #e7f1ff;
        border: 1px solid #b8d4ff;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .signature-box p {
        margin: 0.5rem 0 0;
        font-size: 0.875rem;
        color: #6c757d;
    }
    .signature-box .signature-date {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .status-badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        margin-left: 0.5rem;
    }
    .status-active {
        background-color: #198754;
    }
    .status-pending {
        background-color: #ffc107;
        color: #000 !important;
    }
    .status-draft {
        background-color: #6c757d;
    }
    .status-rejected {
        background-color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-1 py-2">
    @if(session('success'))
        @php
            // Clear the success message from session after displaying
            $successMessage = session('success');
            session()->forget('success');
        @endphp
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" id="successAlert">
            <i class="fas fa-check-circle me-2"></i> {{ $successMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="spt-container" style="padding: 0.75rem 1rem;">
        <!-- Header with Logo and Document Title -->
        <div class="spt-show-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo jbm.png') }}" alt="JBM Logo" class="logo">
                <div class="company-info">
                    <h1 class="company-name">PT. JHONLIN BATU MANDIRI</h1>
                    <p class="company-subtitle">Harvest Management System</p>
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
                        'Draft' => 'status-draft',
                        'Ditolak' => 'status-rejected'
                    ][$status] ?? 'status-draft';

                    $approvalStages = [
                        \App\Models\SPT::STAGE_DRAFT => 'Draft',
                        \App\Models\SPT::STAGE_PEMBUAT => 'Menunggu TTD Pemeriksa',
                        \App\Models\SPT::STAGE_PEMERIKSA => 'Menunggu TTD Penyetuju',
                        \App\Models\SPT::STAGE_PENYETUJU => 'Menunggu Konfirmasi Mandor',
                        \App\Models\SPT::STAGE_SELESAI => 'Selesai',
                        \App\Models\SPT::STAGE_DITOLAK => 'Ditolak'
                    ];
                    $currentStage = $spt->approval_stage ?? \App\Models\SPT::STAGE_DRAFT;
                    $stageLabel = $approvalStages[$currentStage] ?? $currentStage;
                @endphp
                
                @php
                    // Cek konfirmasi mandor dan vendor
                    $mandorConfirmation = \App\Models\SptConfirmation::where('spt_id', $spt->id)
                        ->where('role_name', 'mandor')
                        ->first();
                        
                    $vendorConfirmation = \App\Models\SptConfirmation::where('spt_id', $spt->id)
                        ->where('role_name', 'vendor')
                        ->first();
                        
                    // Bangun tooltip content
                    $tooltipContent = '';
                    
                    // Style untuk tooltip
                    $tooltipContent = '<div class=\'text-start\' style=\'min-width: 200px; color: #333;\'>';
                    
                    // Status mandor
                    $tooltipContent .= '<div class=\'mb-2\'>';
                    $tooltipContent .= '<div class=\'fw-bold\'>Status Mandor</div>';
                    $tooltipContent .= $mandorConfirmation ? 
                        '<span class=\'text-success fw-medium\'>✓ Dikonfirmasi</span><br>' .
                        '<div class=\'text-secondary\' style=\'font-size: 0.8rem;\'>' . \Carbon\Carbon::parse($mandorConfirmation->created_at)->translatedFormat('d F Y, H:i') . '</div>' : 
                        '<span class=\'text-secondary\'>Belum dikonfirmasi</span>';
                    $tooltipContent .= '</div>';
                    
                    // Garis pemisah
                    $tooltipContent .= '<hr class=\'my-2\' style=\'opacity: 0.2;\'>';
                    
                    // Status vendor
                    $tooltipContent .= '<div>';
                    $tooltipContent .= '<div class=\'fw-bold\'>Status Vendor</div>';
                    $tooltipContent .= $vendorConfirmation ? 
                        '<span class=\'text-success fw-medium\'>✓ Dikonfirmasi</span><br>' .
                        '<div class=\'text-secondary\' style=\'font-size: 0.8rem;\'>' . \Carbon\Carbon::parse($vendorConfirmation->created_at)->translatedFormat('d F Y, H:i') . '</div>' : 
                        '<span class=\'text-secondary\'>Belum dikonfirmasi</span>';
                    $tooltipContent .= '</div>';
                    
                    $tooltipContent .= '</div>';
                @endphp
                
                @can('view-approval-progress')
                    <div class="d-inline-flex align-items-center">
                        <span class="status-badge {{ $statusClass }}">
                            {{ $status }} - {{ $stageLabel }}
                        </span>

                        
                        @if($mandorConfirmation || $vendorConfirmation)
                        <button type="button" 
                            class="btn btn-sm bg-white border rounded-circle p-1 ms-2 shadow-sm"
                            style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; border-color: #ced4da;"
                            data-bs-toggle="tooltip" 
                            data-bs-html="true"
                            title="{!! $tooltipContent !!}">
                            <i class="fas fa-info" style="font-size: 0.7rem; color: #0d6efd;"></i>
                        </button>
                        @endif
                    </div>
                    
                    @push('scripts')
                    <script>
                        // Inisialisasi tooltip
                        document.addEventListener('DOMContentLoaded', function() {
                            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            tooltipTriggerList.map(function (tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl, {
                                    html: true,
                                    placement: 'right'
                                });
                            });
                        });
                    </script>
                    @endpush
                @endcan
        </div>
    </div>

    <!-- Main Content Table -->
    <table class="spt-show-table">
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
                    {{ $spt->vendor->nama_vendor }} ({{ $spt->kode_vendor }})
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
            <td class="label whitespace-nowrap">Estate /Divisi /Zona</td>
            <td class="whitespace-nowrap">
                {{ $spt->subBlock->estate ?? '-' }} /
                {{ $spt->subBlock->divisi ?? '-' }} /
                @if($spt->subBlock->zona)
                    / Zona {{ $spt->subBlock->zona }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Luas</td>
            <td class="whitespace-nowrap">
                {{ $spt->subBlock->luas_area ? number_format($spt->subBlock->luas_area, 2, ',', '.') . ' Ha' : '-' }}
            </td>
        </tr>

        @endif
        <tr>
            <td class="label">Diawasi Oleh</td>
            <td>
                @if($spt->foremanSubBlock)
                    {{ $spt->foremanSubBlock->nama_mandor }} ({{ $spt->foremanSubBlock->kode_mandor }})
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Jumlah Tenaga</td>
            <td>{{ $spt->jumlah_tenaga_kerja }} orang</td>
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
    <!-- Approval Progress -->
    @can('view-approval-progress')
    <div class="approval-progress mb-4">
        <div class="progress" style="height: 30px;">
            @php
                $stages = [
                    \App\Models\SPT::STAGE_DRAFT => ['label' => 'Draft', 'width' => '20%'],
                    \App\Models\SPT::STAGE_PEMBUAT => ['label' => 'Pembuat', 'width' => '20%'],
                    \App\Models\SPT::STAGE_PEMERIKSA => ['label' => 'Pemeriksa', 'width' => '20%'],
                    \App\Models\SPT::STAGE_PENYETUJU => ['label' => 'Penyetuju', 'width' => '20%'],
                    \App\Models\SPT::STAGE_SELESAI => ['label' => 'Selesai', 'width' => '20%'],
                ];

                $currentIndex = array_search($currentStage, array_keys($stages));
                $currentIndex = $currentIndex !== false ? $currentIndex : 0;
                $progressWidth = ($currentIndex / (count($stages) - 1)) * 100;
            @endphp
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressWidth }}%"
                 aria-valuenow="{{ $progressWidth }}" aria-valuemin="0" aria-valuemax="100">
                {{ round($progressWidth) }}%
            </div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            @foreach($stages as $stage => $data)
                @php
                    $signatureExists = false;
                    if ($stage === \App\Models\SPT::STAGE_PEMBUAT && $spt->ttd_dibuat_oleh_path) {
                        $signatureExists = true;
                    } elseif ($stage === \App\Models\SPT::STAGE_PEMERIKSA && $spt->ttd_diperiksa_oleh_path) {
                        $signatureExists = true;
                    } elseif ($stage === \App\Models\SPT::STAGE_PENYETUJU && $spt->ttd_disetujui_oleh_path) {
                        $signatureExists = true;
                    }

                    $isCompleted = array_search($stage, array_keys($stages)) < $currentIndex ||
                                 $signatureExists ||
                                 ($stage === $currentStage && in_array($stage, [\App\Models\SPT::STAGE_SELESAI, \App\Models\SPT::STAGE_DITOLAK]));
                    $isCurrent = $stage === $currentStage && !$signatureExists;
                    $stageClass = $isCompleted ? 'completed' : ($isCurrent ? 'current' : '');
                @endphp
                <div class="text-center {{ $stageClass }}" style="width: {{ $data['width'] }}">
                    <i class="fas {{ $isCompleted ? 'fa-check-circle' : ($isCurrent ? 'fa-spinner fa-spin' : 'fa-circle') }}"></i>
                    <div class="small mt-1">{{ $data['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endcan

    <div style="display: flex; justify-content: space-between; margin: 1.5rem 0;">
        <!-- Pembuat -->
        <div class="signature-box {{ $currentStage === \App\Models\SPT::STAGE_DRAFT ? 'current-signer' : '' }}" style="width: 30%; text-align: center;">
            @php
                $path = $spt->ttd_dibuat_oleh_path ? str_replace('storage/app/public/', '', $spt->ttd_dibuat_oleh_path) : null;
                $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
            @endphp
            @if($path && file_exists($fullPath))
                <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                     alt="Tanda Tangan"
                     style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto;">
            @else
                <div style="height: 100px; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; margin: 0 auto 0.5rem;">
                    Tanda Tangan
                </div>
            @endif
            <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
            <div style="font-weight: bold;">{{ $spt->dibuat_oleh ?? 'Belum ada' }}</div>
            <div style="color: #6c757d;">Asisten Divisi Plantation</div>
        </div>

        <!-- Pemeriksa -->
        <div class="signature-box {{ $currentStage === \App\Models\SPT::STAGE_PEMBUAT ? 'current-signer' : '' }}" style="width: 30%; text-align: center;">
            @php
                $path = $spt->ttd_diperiksa_oleh_path ? str_replace('storage/app/public/', '', $spt->ttd_diperiksa_oleh_path) : null;
                $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
            @endphp
            @if($path && file_exists($fullPath))
                <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                     alt="Tanda Tangan"
                     style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto;">
            @else
                <div style="height: 100px; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; margin: 0 auto 0.5rem;">
                    Tanda Tangan
                </div>
            @endif
            <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
            <div style="font-weight: bold;">{{ $spt->diperiksa_oleh ?? 'Belum ada' }}</div>
            <div style="color: #6c757d;">Asisten Manager Plantation</div>
        </div>

        <!-- Penyetuju -->
        <div class="signature-box {{ $currentStage === \App\Models\SPT::STAGE_PEMERIKSA ? 'current-signer' : '' }}" style="width: 30%; text-align: center;">
            @php
                $path = $spt->ttd_disetujui_oleh_path ? str_replace('storage/app/public/', '', $spt->ttd_disetujui_oleh_path) : null;
                $fullPath = $path ? storage_path('app/public/' . ltrim($path, '/')) : null;
            @endphp
            @if($path && file_exists($fullPath))
                <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                     alt="Tanda Tangan"
                     style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto;">
            @else
                <div style="height: 100px; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; margin: 0 auto 0.5rem;">
                    Tanda Tangan
                </div>
            @endif
            <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
            <div style="font-weight: bold;">{{ $spt->disetujui_oleh ?? 'Belum ada' }}</div>
            <div style="color: #6c757d;">Manager Plantation</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons mb-4">
        <div class="d-flex flex-wrap gap-2">
            <!-- Navigation Buttons -->
            <a href="{{ route('spt.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>

            <!-- Action Buttons -->
            @php
                $userRole = auth()->user()->role_name;
                $isAdmin = $userRole === 'admin';
                $isDivisiPlantation = $userRole === 'Assistant Divisi Plantation';
                $isAssistantManagerPlantation = $userRole === 'Assistant Manager Plantation';
                $isManagerPlantation = $userRole === 'Manager Plantation';
                
                // Admin will have the same view as Assistant Divisi Plantation
                $showDivisiPlantationActions = $isAdmin || $isDivisiPlantation;
                $showManagerActions = $isAdmin || $isManagerPlantation || $isAssistantManagerPlantation;
            @endphp

            @if($isAdmin || $isDivisiPlantation || $isAssistantManagerPlantation || $isManagerPlantation)
                @if($spt->approval_stage === \App\Models\SPT::STAGE_DRAFT && $showDivisiPlantationActions)
                @php
                    // Check if current user is the creator and hasn't signed yet
                    $showSubmitButton = true;
                    if ($isDivisiPlantation && $spt->ttd_dibuat_oleh) {
                        $showSubmitButton = false;
                    }
                @endphp
                
                @if($showSubmitButton)
                    <form action="{{ route('approval.spt.approve', $spt) }}" method="POST" class="d-inline" id="submitForm">
                        @csrf
                        <input type="hidden" name="status" value="Diajukan">
                        <input type="hidden" name="signature" id="submitSignatureInput">
                        <button type="button" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan
                        </button>
                    </form>
                @endif
            @endif
            

                @if($spt->approval_stage === \App\Models\SPT::STAGE_PEMBUAT && ($isAdmin || $isAssistantManagerPlantation))
                    <form action="{{ route('approval.spt.approve', $spt) }}" method="POST" class="d-inline" id="checkForm">
                        @csrf
                        <input type="hidden" name="status" value="Diperiksa">
                        <input type="hidden" name="signature" id="checkSignatureInput">
                        <button type="button" class="btn btn-info" id="checkBtn">
                            <i class="fas fa-check me-1"></i> Periksa
                        </button>
                    </form>
                @endif

                @if($spt->approval_stage === \App\Models\SPT::STAGE_PEMERIKSA && ($isAdmin || $isManagerPlantation))
                    <form action="{{ route('approval.spt.approve', $spt) }}" method="POST" class="d-inline" id="approveForm">
                        @csrf
                        <input type="hidden" name="status" value="Disetujui">
                        <input type="hidden" name="signature" id="approveSignatureInput">
                        <button type="button" class="btn btn-success" id="approveBtn">
                            <i class="fas fa-check-double me-1"></i> Setuju
                        </button>
                    </form>
                @endif

                @if(($spt->canBeEditedBy(auth()->user()) || $isAdmin) && $spt->approval_stage === \App\Models\SPT::STAGE_DRAFT)
                    <a href="{{ route('spt.edit', $spt->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                @endif

                @if(($spt->canBeDeletedBy(auth()->user()) || $isAdmin) && $spt->approval_stage === \App\Models\SPT::STAGE_DRAFT)
                    <form action="{{ route('spt.destroy', $spt->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger delete-btn" data-id="{{ $spt->id }}" onclick="return confirm('Apakah Anda yakin ingin menghapus SPT ini?')">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </form>
                @endif


            @endif

            @if(in_array($spt->status, ['Draft', 'Ditolak', 'Diajukan', 'Diperiksa', 'Disetujui', 'Selesai']))
                <a href="{{ route('spt.download-pdf', $spt->id) }}" class="btn btn-info text-white">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            @endif
        </div>
    </div>

    <!-- Signature Modal -->
    <div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signatureModalLabel">Tanda Tangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <canvas id="signatureCanvas" class="signature-canvas" height="200"></canvas>
                        <input type="hidden" name="signature">
                    </div>
                    <div class="signature-pad-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignature">
                            <i class="fas fa-eraser me-1"></i> Hapus
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmSignature">Simpan & Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .signature-canvas {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        width: 100%;
        background-color: #f8f9fa;
    }
    .signature-pad-actions {
        margin-top: 10px;
        text-align: right;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    // Initialize variables in the global scope
    let signaturePad = null;
    let currentForm = null;
    let resizeTimeout = null;
    let modal = null;
    let modalElement = null;
    let signatureCanvas = null;

    // Handle canvas resizing
    function resizeCanvas(canvas, container) {
        if (!canvas || !container) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const width = container.offsetWidth;
        const height = 200; // Fixed height for signature area

        canvas.width = width * ratio;
        canvas.height = height * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        // Adjust canvas display size
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;

        if (signaturePad) {
            signaturePad.clear();
        }
    }

    // Handle window resize with debounce
    function handleResize() {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        resizeTimeout = setTimeout(() => {
            const canvas = document.getElementById('signatureCanvas');
            const container = canvas ? canvas.parentElement : null;
            if (canvas && container) {
                resizeCanvas(canvas, container);
            }
        }, 200);
    }

    // Make initSignaturePad globally accessible
    window.initSignaturePad = function() {
        signatureCanvas = document.getElementById('signatureCanvas');
        if (!signatureCanvas) {
            console.error('Signature canvas not found');
            return;
        }

        // Clear any existing signature pad
        if (signaturePad) {
            signaturePad.off();
            signaturePad = null;
        }

        // Ensure canvas is properly sized
        const container = signatureCanvas.parentElement;
        if (container) {
            resizeCanvas(signatureCanvas, container);
        }

        // Initialize signature pad with better defaults
        signaturePad = new SignaturePad(signatureCanvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16,
            velocityFilterWeight: 0.7,
            minDistance: 1
        });

        // Clear the canvas initially
        signaturePad.clear();

        // Update clear button state
        const clearBtn = document.getElementById('clearSignature');
        if (clearBtn) {
            clearBtn.disabled = true;

            // Handle clear button click
            clearBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (signaturePad) {
                    signaturePad.clear();
                    this.disabled = true;
                }
                return false;
            };
        }

        // Update clear button state when signature changes
        signaturePad.onBegin = function() {
            if (clearBtn) clearBtn.disabled = false;
            return true;
        };

        signaturePad.onEnd = function() {
            if (clearBtn) clearBtn.disabled = signaturePad.isEmpty();
            return true;
        };

        // Enable touch support
        signaturePad.addEventListener('touchstart', function() {}, { passive: true });
    };

    // Initialize everything when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize modal and canvas elements
        modalElement = document.getElementById('signatureModal');
        signatureCanvas = document.getElementById('signatureCanvas');
        const clearBtn = document.getElementById('clearSignature');

        // Initialize modal if element exists
        if (modalElement) {
            modal = new bootstrap.Modal(modalElement);

            // Style the signature canvas
            if (signatureCanvas) {
                signatureCanvas.style.border = '1px solid #ddd';
                signatureCanvas.style.borderRadius = '4px';
                signatureCanvas.style.width = '100%';
                signatureCanvas.style.height = '200px';
                signatureCanvas.style.touchAction = 'none'; // Important for touch devices

                // Prevent scrolling when touching the canvas
                signatureCanvas.addEventListener('touchmove', function(e) {
                    e.preventDefault();
                }, { passive: false });
            }

            // Handle modal shown event to properly initialize signature pad
            modalElement.addEventListener('shown.bs.modal', function() {
                // Small delay to ensure modal is fully visible
                setTimeout(() => {
                    if (signatureCanvas) {
                        // Initialize the signature pad
                        window.initSignaturePad();

                        // Ensure canvas is properly sized
                        const container = signatureCanvas.parentElement;
                        if (container) {
                            resizeCanvas(signatureCanvas, container);
                        }
                    }
                }, 100);
            });

            // Handle modal hidden event to clean up
            modalElement.addEventListener('hidden.bs.modal', function() {
                if (signaturePad) {
                    signaturePad.off();
                    signaturePad = null;
                }
                currentForm = null;
            });

            // Handle window resize
            window.addEventListener('resize', handleResize);
        }

        // Clear button handler is now in initSignaturePad

        // Canvas resizing is now handled by the global functions

        // Handle confirm signature button
        const confirmBtn = document.getElementById('confirmSignature');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', async function() {
                if (!signaturePad || signaturePad.isEmpty()) {
                    alert('Harap beri tanda tangan terlebih dahulu');
                    return;
                }

                if (!currentForm) {
                    console.error('No form found for submission');
                    alert('Terjadi kesalahan. Silakan muat ulang halaman dan coba lagi.');
                    return;
                }

                // Get the signature data
                const signatureInput = currentForm.querySelector('input[name="signature"]');
                if (!signatureInput) {
                    console.error('Signature input field not found');
                    alert('Terjadi kesalahan. Silakan muat ulang halaman dan coba lagi.');
                    return;
                }

                try {
                    // Set the signature data
                    signatureInput.value = signaturePad.toDataURL('image/png');

                    // Get the original button that triggered the modal
                    const originalButton = document.activeElement;
                    const originalButtonText = originalButton?.innerHTML;

                    // Disable buttons and show loading state
                    if (originalButton) {
                        originalButton.disabled = true;
                        originalButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
                    }
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

                    // Hide the modal
                    if (modal) {
                        modal.hide();
                    }

                    // Submit the form
                    const formData = new FormData(currentForm);
                    const response = await fetch(currentForm.action, {
                        method: currentForm.method,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    // Show success message
                    if (result.success) {
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message || 'Tanda tangan berhasil disimpan.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        // Force a full page reload to update the progress bar and signature status
                        setTimeout(() => {
                            window.location.href = window.location.href.split('?')[0] + '?success=' + encodeURIComponent(result.message || 'Tanda tangan berhasil disimpan.');
                        }, 1500);
                    } else {
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: result.message || 'Terjadi kesalahan saat menyimpan tanda tangan.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        // If there's a redirect, use it, otherwise reload
                        if (result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan: ' + (error.message || 'Silakan coba lagi'));

                    // Reset buttons
                    if (originalButton) {
                        originalButton.disabled = false;
                        originalButton.innerHTML = originalButtonText;
                    }
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Simpan Tanda Tangan';
                }
            });
        }

        // Clear button event handler is now consolidated above

        // Clean up event listeners when the page is unloaded
        window.addEventListener('beforeunload', function() {
            window.removeEventListener('resize', handleResize);
            if (signaturePad) {
                signaturePad.off();
                signaturePad = null;
            }
            currentForm = null;
        });
    });

    // Canvas resizing is now handled by the global functions

    // Handle action buttons with event delegation
    document.addEventListener('click', function handleButtonClick(e) {
        // Handle signature buttons
        const button = e.target.closest('#submitBtn, #checkBtn, #approveBtn');
        if (!button) return;

        const form = button.closest('form');
        if (!form) return;

        e.preventDefault();
        currentForm = form;

        // Store the original button text if not already stored
        if (!button.hasAttribute('data-original-text')) {
            button.setAttribute('data-original-text', button.innerHTML);
        }

        // Initialize or clear the signature pad
        if (!signaturePad) {
            window.initSignaturePad();
        } else {
            signaturePad.clear();
        }

        // Set modal title based on button text
        const modalTitle = document.getElementById('signatureModalLabel');
        if (modalTitle) {
            const buttonText = button.textContent.trim();
            modalTitle.textContent = `Konfirmasi ${buttonText}`;
        }

        // Show the modal
        if (modal) {
            modal.show();
        }
    });

    // Close alert message
    document.addEventListener('click', function(e) {
        const closeBtn = e.target.closest('.close-alert');
        if (closeBtn) {
            const alertMsg = closeBtn.closest('.alert-message');
            if (alertMsg) {
                alertMsg.remove();
            }
        }
    });
</script>
@endpush
@endsection
