@extends('layouts.master')

@section('page-title', 'Konfirmasi Data BAPP Tebang')

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
        transition: background-color 0.2s;
    }
    .btn-confirm:hover {
        background-color: #059669;
    }
    .btn-back {
        background-color: #6b7280;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        margin-right: 0.75rem;
        transition: background-color 0.2s;
    }
    .btn-back:hover {
        background-color: #4b5563;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Konfirmasi Data BAPP Tebang</h1>
        <!-- <div class="flex space-x-2">
            <a href="{{ route('hasil-tebang.show', $vendor->kode_vendor) }}" class="btn-back">
                Kembali
            </a>
        </div> -->
    </div>

    <form action="{{ route('bapp.store') }}" method="POST" id="bappForm">
        @method('POST')
        @csrf
        <input type="hidden" name="hasil_tebang_ids" value="{{ json_encode($hasilTebangIds) }}">
        <input type="hidden" name="vendor_kode" value="{{ $vendor->kode_vendor }}">

        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="p-4 bg-gray-50 border-b">
                <h2 class="text-lg font-medium text-gray-800">Rangkuman Data</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Tebang</label>
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
                        <label for="divisi" class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                        <input type="text" name="divisi" id="divisi"
                               value="{{ $hasilTebang[0]->divisi ?? '' }}"
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tonase (Kg)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sortase (Kg)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tonase Final (Kg)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tebang (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ikat/Tumpuk (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Muat (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sewa Grab (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insentif Pasok (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insentif Beras & TK (Rp)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pendapatan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalTonaseFinal = 0;
                            $totalTebang = 0;
                            $totalIkatTumpuk = 0;
                            $totalMuat = 0;
                            $totalSewaGrab = 0;
                            $totalInsentifPasok = 0;
                            $totalInsentifBerasTk = 0;
                            $totalPendapatan = 0;
                        @endphp

                        @foreach($hasilTebang as $item)
                            @php
                                $tonaseFinal = $item->netto2;
                                $tebang = $tonaseFinal * 54000; // 54 rupiah per kg
                                $ikatTumpuk = $tonaseFinal * 15000; // 15 rupiah per kg
                                $muat = $tonaseFinal * 54000; // 54 rupiah per kg
                                $sewaGrab = $item->sewa_grab; // This should be set from the database or form input
                                $insentifPasok = $tonaseFinal * 9000; // 9 rupiah per kg
                                $insentifBerasTk = $tonaseFinal * 6000; // 6 rupiah per kg
                                $totalRow = $tebang + $ikatTumpuk + $muat + $insentifPasok + $insentifBerasTk - $sewaGrab;

                                // Add to totals
                                $totalTonaseFinal += $tonaseFinal;
                                $totalTebang += $tebang;
                                $totalIkatTumpuk += $ikatTumpuk;
                                $totalMuat += $muat;
                                $totalSewaGrab += $sewaGrab;
                                $totalInsentifPasok += $insentifPasok;
                                $totalInsentifBerasTk += $insentifBerasTk;
                                $totalPendapatan += $totalRow;
                            @endphp

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->kode_hasil_tebang }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->tanggal_timbang)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->netto1, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->sortase, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($tonaseFinal, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($tebang, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($ikatTumpuk, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($muat, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    Rp {{ number_format($sewaGrab, 0, ',', '.') }}
                                    <input type="hidden" name="sewa_grab[]" value="{{ $sewaGrab }}">
                                    <input type="hidden" name="sewa_grab_row_index[]" value="{{ $loop->index }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($insentifPasok, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($insentifBerasTk, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-green-600 total-pendapatan" id="total-{{ $loop->index }}">
                                    Rp {{ number_format($totalRow, 0, ',', '.') }}
                                </td>
                                <input type="hidden" name="total_pendapatan[]" value="{{ $totalRow }}">
                            </tr>
                        @endforeach

                        <!-- Total Row -->
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="2" class="px-6 py-4 whitespace-nowrap text-right">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($hasilTebang->sum('netto1'), 2, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($hasilTebang->sum('sortase'), 2, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($totalTonaseFinal, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($totalTebang, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($totalIkatTumpuk, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($totalMuat, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right" id="total-sewa-grab">Rp {{ number_format($totalSewaGrab, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($totalInsentifPasok, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($totalInsentifBerasTk, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-green-600" id="grand-total">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('bapp.generate-selection', $vendor->kode_vendor) }}" class="btn-back">
                Kembali
            </a>
            <button type="submit" class="btn-confirm">
                Konfirmasi & Generate BAPP Tebang
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default tanggal BAPP to today
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        document.getElementById('tanggal_bapp').value = `${year}-${month}-${day}`;

        // Function to format number with thousand separators
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Update grand total on page load
        updateGrandTotal();

        // Function to update grand total
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('input[name="total_pendapatan[]"]').forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });

            document.getElementById('grand-total').textContent = 'Rp ' + formatNumber(Math.round(grandTotal));
        }
    });
</script>
@endpush

@endsection
