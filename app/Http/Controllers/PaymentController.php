<?php

namespace App\Http\Controllers;

use App\Models\BappAngkut;
use App\Models\BappTebang;
use App\Models\Spd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // public function index()
    // {
    //     // Get completed BAPP Angkut
    //     $bappAngkut = BappAngkut::whereNotNull('ttd_disetujui_oleh_path')
    //         ->with('vendor')
    //         ->get()
    //         ->map(function($item) {
    //             return (object) [
    //                 'id' => 'bapp_angkut_' . $item->kode_bapp,
    //                 'document_type' => 'bapp_angkut',
    //                 'document_id' => $item->kode_bapp,
    //                 'tanggal_pengajuan' => $item->tanggal_bapp,
    //                 'tanggal_pembayaran' => $item->updated_at,
    //                 'no_spd' => $item->no_spd ?? '-',
    //                 'vendor_kode' => $item->vendor_angkut,
    //                 'vendor_nama' => $item->vendor ? $item->vendor->nama_vendor : 'Vendor Tidak Ditemukan',
    //                 'vendor_jenis' => 'Angkut',
    //                 'total' => $item->total_pendapatan,
    //                 'status' => 'Selesai Bayar',
    //                 'notifikasi_dikirim' => $item->notifikasi_dikirim ?? false,
    //             ];
    //         });

    //     // Get completed BAPP Tebang
    //     $bappTebang = BappTebang::where('status', 'Selesai')
    //         ->whereNotNull('ttd_disetujui_oleh_path')
    //         ->with('vendor')
    //         ->get()
    //         ->map(function($item) {
    //             return (object) [
    //                 'id' => 'bapp_tebang_' . $item->kode_bapp,
    //                 'document_type' => 'bapp_tebang',
    //                 'document_id' => $item->kode_bapp,
    //                 'tanggal_pengajuan' => $item->tanggal_bapp,
    //                 'tanggal_pembayaran' => $item->updated_at,
    //                 'no_spd' => $item->no_spd ?? '-',
    //                 'vendor_kode' => $item->vendor_tebang,
    //                 'vendor_nama' => $item->vendor ? $item->vendor->nama_vendor : 'Vendor Tidak Ditemukan',
    //                 'vendor_jenis' => 'Tebang',
    //                 'total' => $item->total_pendapatan,
    //                 'status' => 'Selesai Bayar',
    //                 'notifikasi_dikirim' => $item->notifikasi_dikirim ?? false,
    //             ];
    //         });

    //     // Get SPD that have been paid
    //     $spdPayments = Spd::where('status', 'Selesai')
    //         ->whereNotNull('ttd_dibayar_oleh')
    //         ->get()
    //         ->map(function($spd) {
    //             return (object) [
    //                 'id' => 'spd_' . $spd->no_spd,
    //                 'document_type' => 'spd',
    //                 'document_id' => $spd->no_spd,
    //                 'tanggal_pengajuan' => $spd->created_at,
    //                 'tanggal_pembayaran' => $spd->updated_at,
    //                 'no_spd' => $spd->no_spd,
    //                 'vendor_kode' => null,
    //                 'vendor_nama' => 'Multiple Vendors',
    //                 'vendor_jenis' => 'Multiple',
    //                 'total' => $spd->total_dana,
    //                 'status' => 'Selesai Bayar',
    //                 'notifikasi_dikirim' => $spd->notifikasi_dikirim ?? false,
    //                 'spd' => $spd,
    //             ];
    //         });

    //     // Combine all payments
    //     $payments = $bappAngkut->concat($bappTebang)->concat($spdPayments);

    //     // Sort by payment date descending
    //     $payments = $payments->sortByDesc(function($item) {
    //         return \Carbon\Carbon::parse($item->tanggal_pembayaran)->timestamp;
    //     })->values();

    //     // Convert to pagination
    //     $perPage = 10;
    //     $currentPage = request()->get('page', 1);
    //     $pagedData = $payments->slice(($currentPage - 1) * $perPage, $perPage)->all();
    //     $payments = new \Illuminate\Pagination\LengthAwarePaginator(
    //         $pagedData,
    //         $payments->count(),
    //         $perPage,
    //         $currentPage,
    //         ['path' => request()->url(), 'query' => request()->query()]
    //     );

    //     return view('payment.index', compact('payments'));
    // }

public function index()
{
    $bappAngkut = BappAngkut::whereNotNull('ttd_disetujui_oleh_path')
        ->with('vendor')
        ->get()
        ->map(function ($item) {
            $vendorModel = $item->vendor;
            return (object) [
                'id' => 'bapp_angkut_' . $item->kode_bapp,
                'document_type' => 'bapp_angkut',
                'document_id' => $item->kode_bapp,
                'tanggal_pengajuan' => $item->tanggal_bapp,
                'tanggal_pembayaran' => $item->updated_at,
                'no_spd' => $item->no_spd ?? '-',
                'vendor_kode' => $item->vendor_angkut,
                'vendor_nama' => $vendorModel->nama_vendor ?? 'Vendor Tidak Ditemukan',
                'vendor_jenis' => 'Angkut',
                'total' => $item->total_pendapatan,
                'status' => 'Selesai Bayar',
                'notifikasi_dikirim' => $item->notifikasi_dikirim ?? false,
                'vendors' => [[
                    'kode_bapp' => $item->kode_bapp,
                    'vendor' => $vendorModel,
                    'total_tonase' => $item->total_tonase ?? 0,
                    'total_pendapatan' => $item->total_pendapatan,
                    'type' => 'angkut'
                ]]
            ];
        });

    $bappTebang = BappTebang::where('status', 'Selesai')
        ->whereNotNull('ttd_disetujui_oleh_path')
        ->with('vendor')
        ->get()
        ->map(function ($item) {
            $vendorModel = $item->vendor;
            return (object) [
                'id' => 'bapp_tebang_' . $item->kode_bapp,
                'document_type' => 'bapp_tebang',
                'document_id' => $item->kode_bapp,
                'tanggal_pengajuan' => $item->tanggal_bapp,
                'tanggal_pembayaran' => $item->updated_at,
                'no_spd' => $item->no_spd ?? '-',
                'vendor_kode' => $item->vendor_tebang,
                'vendor_nama' => $vendorModel->nama_vendor ?? 'Vendor Tidak Ditemukan',
                'vendor_jenis' => 'Tebang',
                'total' => $item->total_pendapatan,
                'status' => 'Selesai Bayar',
                'notifikasi_dikirim' => $item->notifikasi_dikirim ?? false,
                'vendors' => [[
                    'kode_bapp' => $item->kode_bapp,
                    'vendor' => $vendorModel,
                    'total_tonase' => $item->total_tonase ?? 0,
                    'total_pendapatan' => $item->total_pendapatan,
                    'type' => 'tebang'
                ]]
            ];
        });

    $spdPayments = Spd::where('status', 'Selesai')
        ->whereNotNull('ttd_dibayar_oleh')
        ->get()
        ->map(function ($spd) {
            // Ambil vendor tebang
            $vendorsTebang = BappTebang::with('vendor')
                ->where('periode_bapp', $spd->periode)
                ->selectRaw('vendor_tebang as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase, MIN(kode_bapp) as kode_bapp')
                ->groupBy('vendor_tebang')
                ->get()
                ->map(function ($item) {
                    $vendor = \App\Models\VendorAngkut::where('kode_vendor', $item->vendor_code)->first();
                    return [
                        'kode_bapp' => $item->kode_bapp,
                        'vendor' => (object)[
                            'kode_vendor' => $item->vendor_code,
                            'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        ],
                        'total_pendapatan' => $item->total_pendapatan,
                        'total_tonase' => $item->total_tonase,
                        'type' => 'tebang'
                    ];
                });

            // Ambil vendor angkut
            $vendorsAngkut = BappAngkut::with('vendor')
                ->where('periode_bapp', $spd->periode)
                ->selectRaw('vendor_angkut as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase, MIN(kode_bapp) as kode_bapp')
                ->groupBy('vendor_angkut')
                ->get()
                ->map(function ($item) {
                    $vendor = \App\Models\VendorAngkut::where('kode_vendor', $item->vendor_code)->first();
                    return [
                        'kode_bapp' => $item->kode_bapp,
                        'vendor' => (object)[
                            'kode_vendor' => $item->vendor_code,
                            'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        ],
                        'total_pendapatan' => $item->total_pendapatan,
                        'total_tonase' => $item->total_tonase,
                        'type' => 'angkut'
                    ];
                });

            return (object) [
                'id' => 'spd_' . $spd->no_spd,
                'document_type' => 'spd',
                'document_id' => $spd->no_spd,
                'tanggal_pengajuan' => $spd->created_at,
                'tanggal_pembayaran' => $spd->updated_at,
                'no_spd' => $spd->no_spd,
                'vendor_kode' => null,
                'vendor_nama' => 'Multiple Vendors',
                'vendor_jenis' => 'Multiple',
                'total' => $spd->total_dana,
                'status' => 'Selesai Bayar',
                'notifikasi_dikirim' => $spd->notifikasi_dikirim ?? false,
                'vendors' => $vendorsTebang->merge($vendorsAngkut)
            ];
        });

    // Gabung semua
    $payments = $bappAngkut->concat($bappTebang)->concat($spdPayments);

    // Urutkan by tanggal pembayaran
    $payments = $payments->sortByDesc(function ($item) {
        return \Carbon\Carbon::parse($item->tanggal_pembayaran)->timestamp;
    })->values();

    // Pagination manual
    $perPage = 10;
    $currentPage = request()->get('page', 1);
    $pagedData = $payments->slice(($currentPage - 1) * $perPage, $perPage)->all();
    $payments = new \Illuminate\Pagination\LengthAwarePaginator(
        $pagedData,
        $payments->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('payment.index', compact('payments'));
}


    public function show($id)
    {
        // Extract the type and document ID from the ID
        $parts = explode('_', $id, 2);
        if (count($parts) !== 2) {
            abort(404, 'Format ID pembayaran tidak valid');
        }

        list($type, $documentId) = $parts;

        if ($type === 'spd') {
            $spd = Spd::with([
                'diajukanOleh',
                'diverifikasiOleh',
                'disetujuiOleh',
                'dibayarOleh',
                'ditolakOleh',
                'diketahuiOleh'
            ])->where('no_spd', $documentId)->firstOrFail();

            // Get vendor data from BAPP Tebang for the SPD's period
            $vendorsTebang = BappTebang::with(['vendor'])
                ->where('periode_bapp', $spd->periode)
                ->selectRaw('vendor_tebang as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
                ->groupBy('vendor_tebang')
                ->get()
                ->map(function($item) {
                    // Get vendor data from VendorAngkut
                    $vendor = \App\Models\VendorAngkut::where('kode_vendor', $item->vendor_code)->first();

                    return [
                        'vendor' => (object)[
                            'kode_vendor' => $item->vendor_code,
                            'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        ],
                        'total_pendapatan' => $item->total_pendapatan,
                        'total_tonase' => $item->total_tonase,
                        'type' => 'tebang',
                        'vendor_model' => $vendor
                    ];
                });

            // Get vendor data from BAPP Angkut for the SPD's period
            $vendorsAngkut = BappAngkut::with(['vendor'])
                ->where('periode_bapp', $spd->periode)
                ->selectRaw('vendor_angkut as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
                ->groupBy('vendor_angkut')
                ->get()
                ->map(function($item) {
                    // Get vendor data from VendorAngkut
                    $vendor = \App\Models\VendorAngkut::where('kode_vendor', $item->vendor_code)->first();

                    return [
                        'vendor' => (object)[
                            'kode_vendor' => $item->vendor_code,
                            'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        ],
                        'total_pendapatan' => $item->total_pendapatan,
                        'total_tonase' => $item->total_tonase,
                        'type' => 'angkut',
                        'vendor_model' => $vendor
                    ];
                });

            // Combine both vendor data
            $vendors = $vendorsTebang->merge($vendorsAngkut);
            $terbilang = $this->terbilang($spd->total_dana) . ' Rupiah';

            return view('payment.show', compact('spd', 'terbilang', 'vendors'));
        } else {
            // Existing code for bapp_angkut and bapp_tebang
            switch ($type) {
                case 'bapp_angkut':
                    $bapp = BappAngkut::with(['vendor', 'spd'])
                        ->where('kode_bapp', $documentId)
                        ->firstOrFail();

                    $vendor = $bapp->vendor;
                    $vendorModel = \App\Models\VendorAngkut::where('kode_vendor', $vendor->kode_vendor)->first();

                    $vendors[] = [
                        'type' => 'angkut',
                        'vendor' => $vendor,
                        'total_tonase' => $bapp->total_tonase ?? 0,
                        'total_pendapatan' => $bapp->total_pendapatan,
                        'kode_bapp' => $bapp->kode_bapp,
                        'vendor_model' => $vendorModel
                    ];

                    $spd = $bapp->spd;
                    $terbilang = $this->terbilang($bapp->total_pendapatan) . ' Rupiah';
                    break;

                case 'bapp_tebang':
                    $bapp = BappTebang::with(['vendor', 'spd'])
                        ->where('kode_bapp', $documentId)
                        ->firstOrFail();

                    $vendor = $bapp->vendor;
                    $vendorModel = \App\Models\VendorTebang::where('kode_vendor', $vendor->kode_vendor)->first();

                    $vendors[] = [
                        'type' => 'tebang',
                        'vendor' => $vendor,
                        'total_tonase' => $bapp->total_tonase ?? 0,
                        'total_pendapatan' => $bapp->total_pendapatan,
                        'kode_bapp' => $bapp->kode_bapp,
                        'vendor_model' => $vendorModel
                    ];

                    $spd = $bapp->spd;
                    $terbilang = $this->terbilang($bapp->total_pendapatan) . ' Rupiah';
                    break;

                default:
                    abort(404, 'Jenis pembayaran tidak valid');
            }

            if (!isset($spd)) {
                abort(404, 'Data SPD tidak ditemukan');
            }

            return view('payment.show', [
                'spd' => $spd,
                'vendors' => $vendors,
                'terbilang' => $terbilang
            ]);
        }
    }

    public function sendNotification($documentType, $documentId)
    {
        // TODO: Implement notification logic based on document type
        // For now, we'll just return success

        return redirect()->back()
            ->with('success', 'Notifikasi telah dikirim');
    }

    private function terbilang($number)
    {
        $number = abs($number);
        $bilangan = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');

        if ($number < 12) {
            return $bilangan[$number];
        } elseif ($number < 20) {
            return $this->terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            $hasil_bagi = (int)($number / 10);
            $hasil_mod = $number % 10;
            return trim(sprintf('%s puluh %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
        } elseif ($number < 200) {
            return sprintf('seratus %s', $this->terbilang($number - 100));
        } elseif ($number < 1000) {
            $hasil_bagi = (int)($number / 100);
            $hasil_mod = $number % 100;
            return trim(sprintf('%s ratus %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
        } elseif ($number < 2000) {
            return trim(sprintf('seribu %s', $this->terbilang($number - 1000)));
        } elseif ($number < 1000000) {
            $hasil_bagi = (int)($number / 1000);
            $hasil_mod = $number % 1000;
            return trim(sprintf('%s ribu %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } elseif ($number < 1000000000) {
            $hasil_bagi = (int)($number / 1000000);
            $hasil_mod = $number % 1000000;
            return trim(sprintf('%s juta %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } elseif ($number < 1000000000000) {
            $hasil_bagi = (int)($number / 1000000000);
            $hasil_mod = $number % 1000000000;
            return trim(sprintf('%s milyar %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else {
            return 'Angka terlalu besar';
        }
    }
}