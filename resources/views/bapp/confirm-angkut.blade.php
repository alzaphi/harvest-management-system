@extends('layouts.master')

@section('page-title', 'Konfirmasi Data BAPP Angkut')

@push('styles')
<style>
    .summary-card {
        background-color: #f9fafb;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .summary-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .summary-item {
        background-color: white;
        border-radius: 0.375rem;
        padding: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    .summary-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.05em;
    }
    .summary-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-top: 0.25rem;
    }
    .btn-confirm {
        background-color: #10b981;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
    }
    .btn-back {
        color: #4b5563;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid #d1d5db;
        background-color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
    }
    .btn-back:hover {
        background-color: #f3f4f6;
    }
</style>
@endpush

@section('content')
@php
    // Pastikan variabel-variabel ini ada dan memiliki nilai default
    $hasilTebang = $hasilTebang ?? collect();
    $hasil_tebang_ids = $hasil_tebang_ids ?? [];
    $vendor = $vendor ?? null;
    $lastBappId = $lastBappId ?? 0;

    // Inisialisasi variabel untuk divisi dan petak
    $divisis = $hasilTebang->pluck('divisi')->unique()->filter()->values() ?? collect();
    $petaks = $hasilTebang->pluck('kode_petak')->unique()->filter()->values() ?? collect();
@endphp

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Konfirmasi Data BAPP Angkut</h1>
    </div>

    @if(!$vendor)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Data vendor tidak ditemukan.</span>
        </div>
    @elseif($hasilTebang->isEmpty())
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Perhatian!</strong>
            <span class="block sm:inline">Tidak ada data hasil tebang yang dipilih.</span>
        </div>
    @else
    <form action="{{ route('bapp.angkut.store') }}" method="POST" id="bappForm">
        @csrf

        <!-- Data yang diperlukan untuk BAPP -->
        <input type="hidden" name="kode_bapp" value="BAPP-A-{{ date('Ymd') }}-{{ str_pad($lastBappId + 1, 4, '0', STR_PAD_LEFT) }}">
        <input type="hidden" name="hasil_tebang_ids" value="{{ json_encode($hasil_tebang_ids) }}">
        <input type="hidden" name="kode_lambung" value="{{ $hasilTebang->first()->kode_lambung }}">

        @foreach($hasil_tebang_ids as $id)
            <input type="hidden" name="hasil_tebang_ids[]" value="{{ $id }}">
        @endforeach

        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 border-b">
                <h2 class="text-lg font-medium text-gray-800">Rangkuman Data</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Angkut</label>
                        <p class="text-gray-900">{{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Data Dipilih</label>
                        <p class="text-gray-900">{{ count($hasilTebang) }} Data</p>
                    </div>
                    <div>
                        <label for="tanggal_bapp" class="block text-sm font-medium text-gray-700 mb-1">Tanggal BAPP</label>
                        <input type="date" name="tanggal_bapp" id="tanggal_bapp"
                               value="{{ now()->format('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                    </div>
                    <div>
                        <label for="periode_bapp" class="block text-sm font-medium text-gray-700 mb-1">Periode BAPP</label>
                        <select name="periode_bapp" id="periode_bapp"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="jenis_tebang" class="block text-sm font-medium text-gray-700 mb-1">Jenis Tebang</label>
                        <input type="text" name="jenis_tebang" id="jenis_tebang"
                               value="{{ $hasilTebang[0]->jenis_tebang ?? '' }}"
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               readonly>
                    </div>
                    <div>
                        <label for="kode_petak" class="block text-sm font-medium text-gray-700 mb-1">Kode Petak</label>
                        <input type="text" name="kode_petak" id="kode_petak"
                               value="{{ $hasilTebang[0]->kode_petak ?? '' }}"
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               readonly>
                    </div>
                    <div>
                        <label for="divisi" class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                        <input type="text" name="divisi" id="divisi"
                               value="{{ $hasilTebang[0]->divisi ?? '' }}"
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               readonly>
                    </div>

                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Total Tonase (Kg)</div>
                        <div class="summary-value">{{ number_format($hasilTebang->sum('netto1'), 2, ',', '.') }}</div>
                        <input type="hidden" name="tonase" value="{{ $hasilTebang->sum('netto1') }}">
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Sortase (Kg)</div>
                        <div class="summary-value">{{ number_format($hasilTebang->sum('sortase'), 2, ',', '.') }}</div>
                        <input type="hidden" name="sortase" value="{{ $hasilTebang->sum('sortase') }}">
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Tonase Final (Kg)</div>
                        @php
                            $totalTonase = $hasilTebang->sum('netto1');
                            $totalSortase = $hasilTebang->sum('sortase');
                            $totalTonaseFinal = $totalTonase - $totalSortase;
                        @endphp
                        <div class="summary-value">{{ number_format($totalTonaseFinal, 2, ',', '.') }}</div>
                        <input type="hidden" name="tonase" value="{{ $totalTonase }}">
                        <input type="hidden" name="sortase" value="{{ $totalSortase }}">
                        <input type="hidden" name="tonase_final" value="{{ $totalTonaseFinal }}">
                        <input type="hidden" name="jenis_tebang" value="{{ $hasilTebang[0]->jenis_tebang ?? '' }}">
                        <input type="hidden" name="divisi" value="{{ $hasilTebang[0]->divisi ?? '' }}">
                        <input type="hidden" name="kode_petak" value="{{ $hasilTebang[0]->kode_petak ?? '' }}">
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 border-b">
                <h2 class="text-lg font-medium text-gray-800">Detail Data Hasil Tebang</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Hasil Tebang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Timbang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Lambung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zonasi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tonase (TON)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sortase (TON)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tonase Final (TON)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Insentif Tandem Harvester</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pendapatan (RP)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalBruto = 0;
                            $totalTarra = 0;
                            $totalNetto = 0;
                            $totalSortase = 0;
                            $totalTonaseFinal = 0;
                            $totalPendapatan = 0;
                            $totalInsentif = 0;
                            $totalSemua = 0;

                            // Fungsi untuk menghitung pendapatan berdasarkan zonasi
                            function hitungPendapatan($zonasi, $tonaseFinal) {
                                $hargaPerTon = 0;

                                // Konversi ke float untuk memastikan perbandingan numerik
                                $tonase = (float) $tonaseFinal;

                                // Cek zonasi dan tentukan harga per ton
                                if (strpos(strtolower($zonasi), '1') !== false) {
                                    $hargaPerTon = 35000; // Zonasi 1
                                } elseif (strpos(strtolower($zonasi), '2') !== false) {
                                    $hargaPerTon = 42000; // Zonasi 2
                                } elseif (strpos(strtolower($zonasi), '3') !== false) {
                                    $hargaPerTon = 46000; // Zonasi 3
                                } elseif (strpos(strtolower($zonasi), '4') !== false) {
                                    $hargaPerTon = 55000; // Zonasi 4
                                } else {
                                    // Default jika zonasi tidak dikenali
                                    $hargaPerTon = 0;
                                }

                                // Hitung pendapatan (tonase dalam ton, harga per ton)
                                return $tonase * $hargaPerTon;
                            }
                        @endphp

                        @foreach($hasilTebang as $item)
                            @php
                                $tonaseFinal = $item->netto2;
                                $pendapatan = hitungPendapatan($item->zonasi, $tonaseFinal);
                                $insentif = $item->insentif_tandem_harvester ?? 0;
                                $total = $pendapatan + $insentif;

                                // Akumulasi total
                                $totalBruto += $item->bruto ?? 0;
                                $totalTarra += $item->tarra ?? 0;
                                $totalNetto += $item->netto1 ?? 0;
                                $totalSortase += $item->sortase ?? 0;
                                $totalTonaseFinal += $tonaseFinal;
                                $totalPendapatan += $pendapatan;
                                $totalInsentif += $insentif;
                                $totalSemua += $total;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->kode_hasil_tebang }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($item->tanggal_timbang)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->kode_lambung ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->zonasi ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->netto1, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->sortase, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($tonaseFinal, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp {{ number_format($pendapatan, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp {{ number_format($insentif, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                    Rp {{ number_format($total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Baris Total -->
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                Total
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                Rp {{ number_format($totalInsentif, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium bg-yellow-50">
                                Rp {{ number_format($totalSemua, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('bapp.angkut.generate', $vendor->kode_vendor) }}" class="btn-back">
                Kembali
            </a>
            <button type="submit" class="btn-confirm ml-3">
                Konfirmasi & Generate BAPP Angkut
            </button>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bappForm');

        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
                }
            });
        }
    });
</script>
@endpush
@endsection
