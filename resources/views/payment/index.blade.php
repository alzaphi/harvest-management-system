@extends('layouts.master')

@section('page-title', 'Riwayat Pembayaran')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Riwayat Pembayaran</h1>

    {{-- Tabel vendor langsung tampil --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-2 py-1 border">No. SPD</th>
                    <th class="px-2 py-1 border">Kode BAPP</th>
                    <th class="px-2 py-1 border">Vendor</th>
                    <th class="px-2 py-1 border">Jenis</th>
                    <th class="px-2 py-1 border">Total Tonase</th>
                    <th class="px-2 py-1 border">Total Pendapatan</th>
                    <th class="px-2 py-1 border">Tanggal Pengajuan</th>
                    <th class="px-2 py-1 border">Tanggal Pembayaran</th>
                    <th class="px-2 py-1 border">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    @if(!empty($payment->vendors))
                        @foreach($payment->vendors as $vendor)
                            <tr>
                                <td class="px-2 py-1 border">{{ $payment->no_spd ?? '-' }}</td>
                                <td class="px-2 py-1 border">{{ $vendor['kode_bapp'] ?? '-' }}</td>
                                <td class="px-2 py-1 border">{{ $vendor['vendor']->nama_vendor ?? '-' }}</td>
                                <td class="px-2 py-1 border capitalize">{{ $vendor['type'] ?? '-' }}</td>
                                <td class="px-2 py-1 border text-right">{{ number_format($vendor['total_tonase'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-2 py-1 border text-right">Rp {{ number_format($vendor['total_pendapatan'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-2 py-1 border">
                                    {{ $payment->tanggal_pengajuan ? \Carbon\Carbon::parse($payment->tanggal_pengajuan)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-2 py-1 border">
                                    {{ $payment->tanggal_pembayaran ? \Carbon\Carbon::parse($payment->tanggal_pembayaran)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-2 py-1 border">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($payment->status == 'Selesai Bayar') bg-green-100 text-green-700
                                        @elseif($payment->status == 'Menunggu') bg-yellow-100 text-yellow-700
                                        @else bg-red-100 text-red-700 @endif">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">Tidak ada data vendor</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection