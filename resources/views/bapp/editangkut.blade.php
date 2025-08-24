@extends('layouts.master')

@section('page-title', 'Berita Acara Penerimaan dan Pemeriksaan (BAPP) Angkut - Edit Komplain')

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
    .form-control {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .btn-save {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }
    .btn-cancel {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .action-buttons {
        margin-top: 20px;
        text-align: right;
    }
    .komplain-section {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    .komplain-item {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    .komplain-item:last-child {
        border-bottom: none;
    }
    .signature-canvas {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 10px;
    }
    .signature-clear {
        margin-top: 10px;
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
                'Draft' => 'bg-gray-200 text-gray-800',
                'Diajukan' => 'bg-blue-100 text-blue-800',
                'Diperiksa' => 'bg-yellow-100 text-yellow-800',
                'Disetujui' => 'bg-green-100 text-green-800',
                'Ditolak' => 'bg-red-100 text-red-800',
                'Selesai' => 'bg-purple-100 text-purple-800'
            ][$status] ?? 'bg-gray-200 text-gray-800';
        @endphp
        <span class="status-badge {{ $statusClass }}">
            {{ ucfirst($status) }}
        </span>
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
        @if($bapp->spd && $bapp->spd->sopir)
        <tr>
            <td>NAMA SUPIR</td>
            <td>: {{ $bapp->spd->sopir->nama_sopir ?? '-' }}</td>
            <td>NO. POLISI</td>
            <td>: {{ $bapp->spd->sopir->kendaraan->plat_nomor ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <!-- TABEL DETAIL -->
    <h5 style="margin-top: 1rem;">DATA HASIL TEBANG</h5>
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
            @if($bappAngkutList->count() > 0)
                <tr style="font-weight: bold;">
                    <td colspan="7" style="text-align: center;">TOTAL</td>
                    <td>{{ number_format($bappAngkutList->sum('tonase'), 2, ',', '.') }}</td>
                    <td>{{ number_format($bappAngkutList->sum('sortase'), 2, ',', '.') }}</td>
                    <td>{{ number_format($bappAngkutList->sum('tonase_final'), 2, ',', '.') }}</td>
                    <td>Rp {{ number_format($bappAngkutList->sum('ongkos_angkut'), 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($bappAngkutList->sum('total_pendapatan'), 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- REKAP TONASE ANGKUT -->
    <h5 style="margin-top: 1rem;">REKAP TONASE ANGKUT</h5>
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
            @if(count($grouped) > 0)
                <tr style="font-weight: bold;">
                    <td colspan="4" style="text-align: center;">JUMLAH</td>
                    <td>{{ number_format(array_sum(array_column($grouped, 'tonase_final')), 2, ',', '.') }}</td>
                    <td>Rp {{ number_format(array_sum(array_column($grouped, 'rupiah')), 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Komplain Section -->
    <div class="komplain-section">
        <h4 style="margin-bottom: 1.5rem;">FORM KOMPLAIN</h4>
        <form action="{{ route('bapp.komplain.update', ['jenis' => 'angkut', 'bapp' => $bapp->kode_bapp]) }}" method="POST">
            @csrf
            @method('PUT')

            @if(isset($bapp->komplain) && $bapp->komplain->count() > 0)
                @foreach($bapp->komplain as $index => $komplain)
                    <div class="komplain-item">
                        <div class="form-group">
                            <label for="komplain_{{ $index }}" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Komplain #{{ $index + 1 }}</label>
                            <input type="hidden" name="komplain_id[]" value="{{ $komplain->complain_id }}">
                            <textarea class="form-control" id="komplain_{{ $index }}" name="komplain[]" rows="3" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">{{ old('komplain.'.$index, $komplain->deskripsi) }}</textarea>
                            <small class="text-muted" style="display: block; margin-top: 0.5rem; font-size: 0.85rem;">
                                Dibuat pada: {{ \Carbon\Carbon::parse($komplain->tanggal)->format('d/m/Y H:i') }} |
                                Oleh: {{ $komplain->createdBy->name ?? 'Sistem' }}
                            </small>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="komplain-item">
                    <div class="form-group">
                        <label for="komplain_0" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Komplain #1</label>
                        <input type="hidden" name="komplain_id[]" value="new">
                        <textarea class="form-control" id="komplain_0" name="komplain[]" rows="3" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">{{ old('komplain.0', '') }}</textarea>
                    </div>
                </div>
            @endif

            <div id="additional-komplain"></div>



            <div class="action-buttons">
                <a href="{{ route('bapp.index', ['jenis' => 'angkut', 'bapp' => $bapp->kode_bapp]) }}" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- Signature Section -->
    <div style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid #dee2e6;">
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 30%; text-align: center;">
                <div style="border-top: 1px solid #000; margin: 0 auto; width: 80%;"></div>
                <div style="min-height: 80px; margin: 1rem 0;">
                    @if(!empty($bapp->ttd_diajukan_oleh_path) && Storage::disk('public')->exists($bapp->ttd_diajukan_oleh_path))
                        <img src="{{ asset('storage/' . $bapp->ttd_diajukan_oleh_path) }}" alt="Tanda Tangan" style="max-width: 100%; max-height: 80px;">
                    @endif
                </div>
                <div style="font-weight: 600; margin-top: 0.5rem;">{{ $bapp->diajukan_oleh ?? '-' }}</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Ast. Mgr. Plantation</div>
            </div>

            <div style="width: 30%; text-align: center;">
                <div style="border-top: 1px solid #000; margin: 0 auto; width: 80%;"></div>
                <div style="min-height: 80px; margin: 1rem 0;">
                    @if(!empty($bapp->ttd_diperiksa_oleh_path) && Storage::disk('public')->exists($bapp->ttd_diperiksa_oleh_path))
                        <img src="{{ asset('storage/' . $bapp->ttd_diperiksa_oleh_path) }}" alt="Tanda Tangan" style="max-width: 100%; max-height: 80px;">
                    @endif
                </div>
                <div style="font-weight: 600; margin-top: 0.5rem;">{{ $bapp->diperiksa_oleh ?? '-' }}</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Vendor</div>
            </div>

            <div style="width: 30%; text-align: center;">
                <div style="border-top: 1px solid #000; margin: 0 auto; width: 80%;"></div>
                <div style="min-height: 80px; margin: 1rem 0;">
                    @if(!empty($bapp->ttd_disetujui_oleh_path) && Storage::disk('public')->exists($bapp->ttd_disetujui_oleh_path))
                        <img src="{{ asset('storage/' . $bapp->ttd_disetujui_oleh_path) }}" alt="Tanda Tangan" style="max-width: 100%; max-height: 80px;">
                    @endif
                </div>
                <div style="font-weight: 600; margin-top: 0.5rem;">{{ $bapp->disetujui_oleh ?? '-' }}</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Mgr. Plantation</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new komplain field
        let komplainCount = {{ $bapp->komplain ? $bapp->komplain->count() : 1 }};

        document.getElementById('add-komplain').addEventListener('click', function() {
            komplainCount++;
            const newKomplain = document.createElement('div');
            newKomplain.className = 'komplain-item';
            newKomplain.innerHTML = `
                <div class="form-group">
                    <label for="komplain_${komplainCount}" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Komplain #${komplainCount}</label>
                    <input type="hidden" name="komplain_id[]" value="new">
                    <div style="display: flex;">
                        <textarea class="form-control" id="komplain_${komplainCount}" name="komplain[]" rows="3" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px 0 0 4px;"></textarea>
                        <button type="button" class="btn btn-danger remove-komplain" style="border-radius: 0 4px 4px 0; height: 38px; align-self: flex-end;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            document.getElementById('additional-komplain').appendChild(newKomplain);

            // Add event listener to the new remove button
            newKomplain.querySelector('.remove-komplain').addEventListener('click', function() {
                this.closest('.komplain-item').remove();
                // Renumber remaining komplains
                const komplains = document.querySelectorAll('.komplain-item');
                komplains.forEach((item, index) => {
                    const label = item.querySelector('label');
                    if (label) {
                        label.textContent = `Komplain #${index + 1}`;
                    }
                });
                komplainCount = komplains.length;
            });
        });

        // Add event delegation for dynamically added remove buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-komplain') || e.target.closest('.remove-komplain')) {
                const button = e.target.classList.contains('remove-komplain') ? e.target : e.target.closest('.remove-komplain');
                button.closest('.komplain-item').remove();
                // Renumber remaining komplains
                const komplains = document.querySelectorAll('.komplain-item');
                komplains.forEach((item, index) => {
                    const label = item.querySelector('label');
                    if (label) {
                        label.textContent = `Komplain #${index + 1}`;
                    }
                });
                komplainCount = komplains.length;
            }
        });
    });
</script>
@endpush

@endsection
