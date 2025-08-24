<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BappAngkut;
use App\Models\HasilTebang;
use App\Models\VendorAngkut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BappAngkutController extends Controller
{
    /**
     * INDEX: Daftar semua BAPP Angkut
     */
    public function index()
    {
        $bapps = BappAngkut::with(['vendorAngkut'])->latest()->paginate(10);
        return view('bapp.angkut-index', compact('bapps'));
    }

    /**
     * GENERATE: Pilih hasil tebang untuk vendor tertentu
     */
    public function showGenerateAngkut($vendorKode)
    {
        $vendor = VendorAngkut::where('kode_vendor', $vendorKode)->firstOrFail();

        $hasilTebang = HasilTebang::where('vendor_angkut', $vendorKode)
            ->where(function ($query) {
                $query->whereNull('status_angkut')
                      ->orWhere('status_angkut', '!=', 'Selesai');
            })
            ->orderBy('tanggal_timbang', 'desc')
            ->get();

        return view('bapp.generate-angkut', compact('vendor', 'hasilTebang'));
    }

    /**
     * SHOW CONFIRM: Tampilkan halaman konfirmasi sebelum generate (GET request)
     */
    public function showConfirmAngkut()
    {
        // Redirect back if no data is in the session
        if (!session()->has('bapp_angkut_data')) {
            return redirect()->route('bapp.index', ['jenis' => 'angkut'])
                ->with('error', 'Tidak ada data BAPP yang dapat dikonfirmasi. Silakan pilih data terlebih dahulu.');
        }

        // Get data from session
        $data = session('bapp_angkut_data');

        return view('bapp.confirm-angkut', [
            'vendor' => $data['vendor'],
            'hasilTebang' => $data['hasilTebang'],
            'totalTonase' => $data['totalTonase'],
            'totalSortase' => $data['totalSortase'],
            'totalTonaseFinal' => $data['totalTonaseFinal'],
            'hasil_tebang_ids' => $data['hasil_tebang_ids'],
            'lastBappId' => $data['lastBappId'] ?? 0
        ]);
    }

    /**
     * CONFIRM: Proses konfirmasi (POST request)
     */
    public function confirmAngkut(Request $request)
    {
        $request->validate([
            'hasil_tebang_ids' => 'required|array',
            'hasil_tebang_ids.*' => 'exists:hasil_tebang,kode_hasil_tebang',
            'vendor_kode' => 'required|exists:vendor_angkut,kode_vendor',
        ]);

        $vendor = VendorAngkut::where('kode_vendor', $request->vendor_kode)->firstOrFail();
        $hasilTebang = HasilTebang::whereIn('kode_hasil_tebang', $request->hasil_tebang_ids)->get();

        if ($hasilTebang->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data hasil tebang yang dipilih.');
        }

        // Hitung total
        $totalTonase = $hasilTebang->sum('netto1');
        $totalSortase = $hasilTebang->sum('sortase');
        $totalTonaseFinal = $totalTonase - $totalSortase;
        $totalInsentif = $hasilTebang->sum('insentif_tandem_harvester');

        // Hitung total pendapatan
        $totalPendapatan = 0;
        foreach ($hasilTebang as $item) {
            $totalPendapatan += $this->hitungPendapatan($item->zonasi, ($item->netto1 - $item->sortase) / 1000);
        }

        // Ambil ID terakhir untuk generate kode BAPP
        $lastId = BappAngkut::max('id') ?? 0;

        // Store data in session
        $bappData = [
            'vendor' => $vendor,
            'hasilTebang' => $hasilTebang,
            'totalTonase' => $totalTonase,
            'totalSortase' => $totalSortase,
            'totalTonaseFinal' => $totalTonaseFinal,
            'totalPendapatan' => $totalPendapatan,
            'totalInsentif' => $totalInsentif,
            'hasil_tebang_ids' => $request->hasil_tebang_ids,
            'lastBappId' => $lastId
        ];

        // Store the data in the session
        session(['bapp_angkut_data' => $bappData]);

        // Redirect to the showConfirm method
        return redirect()->route('bapp.angkut.confirm');
    }

    /**
     * STORE: Simpan BAPP Angkut
     */
    public function storeAngkut(Request $request)
    {
        try {
            // Get data from session first
            if (!session()->has('bapp_angkut_data')) {
                return redirect()->route('bapp.index', ['jenis' => 'angkut'])
                    ->with('error', 'Sesi tidak valid. Silakan ulangi proses dari awal.');
            }

            $bappData = session('bapp_angkut_data');

            // Validate request data
            $validated = $request->validate([
                'tanggal_bapp' => 'required|date',
                'kode_lambung' => 'required|string',
                'periode_bapp' => 'required|integer|between:1,12',
            ]);

            DB::beginTransaction();

            // Generate kode BAPP menggunakan periode dari input form
            $periodeBapp = $request->periode_bapp;
            $kodeBapp = $this->generateBappAngkutCode(
                $bappData['vendor']->kode_vendor,
                $periodeBapp
            );

            // Get hasil tebang data from session
            $hasilTebang = $bappData['hasilTebang'];
            $bappAngkutIds = [];

            // Simpan BAPP Angkut untuk setiap hasil tebang
            foreach ($hasilTebang as $item) {
                $bapp = BappAngkut::create([
                    'kode_bapp' => $kodeBapp,
                    'kode_hasil_tebang' => $item->kode_hasil_tebang,
                    'vendor_angkut' => $bappData['vendor']->kode_vendor,
                    'periode_bapp' => (int)$periodeBapp,
                    'tanggal_bapp' => $request->tanggal_bapp,
                    'jenis_tebang' => $item->jenis_tebang,
                    'divisi' => $item->divisi,
                    'kode_petak' => $item->kode_petak,
                    'kode_lambung' => $request->kode_lambung,
                    'zonasi' => $item->zonasi,
                    'tonase' => $item->netto1,
                    'sortase' => $item->sortase,
                    'tonase_final' => $item->netto1 - $item->sortase,
                    'insentif_tandem_harvester' => $item->insentif_tandem_harvester ?? 0, // Beri nilai default 0 jika null
                    'total_pendapatan' => $this->hitungPendapatan($item->zonasi, $item->netto1 - $item->sortase),
                    'status' => 'Draft',
                    'diajukan_oleh' => null,
                    'ttd_diajukan_oleh_path' => null,
                    'diperiksa_oleh' => null,
                    'ttd_diperiksa_oleh_path' => null,
                    'disetujui_oleh' => null,
                    'ttd_disetujui_oleh_path' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $bappAngkutIds[] = $bapp->id;
            }

            // Update status hasil tebang
            HasilTebang::whereIn('kode_hasil_tebang', $bappData['hasil_tebang_ids'])
                ->update([
                    'status_angkut' => 'Generated',
                    'kode_lambung' => $request->kode_lambung,
                    'vendor_angkut' => $bappData['vendor']->kode_vendor
                ]);

            DB::commit();

            // Clear the session data
            session()->forget('bapp_angkut_data');

            // Redirect ke halaman index BAPP dengan pesan sukses
            return redirect()->route('bapp.index', ['jenis' => 'angkut'])
                ->with('success', count($bappAngkutIds) . ' BAPP Angkut berhasil dibuat dengan nomor: ' . $kodeBapp);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error BAPP Angkut: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * SHOW: Tampilkan detail BAPP Angkut
     */
    public function show($id)
    {
        $bapp = BappAngkut::with(['vendorAngkut', 'hasilTebang'])->findOrFail($id);
        return view('bapp.show-angkut', compact('bapp'));
    }

    /**
     * Show the form for editing the specified BAPP Angkut.
     */
    public function edit($kode_bapp)
    {
        $bapp = BappAngkut::with([
            'vendor', // Changed from vendorAngkut to match the relationship name in the model
            'hasilTebang' => function($query) {
                $query->with([
                    'vendorTebang',
                    'vendorAngkut',
                    'subBlock'
                ]);
            },
            'spd.sopir'
        ])->where('kode_bapp', $kode_bapp)->firstOrFail();

        return view('bapp.editangkut', [
            'bapp' => $bapp,
            'bappAngkutList' => BappAngkut::where('kode_bapp', $kode_bapp)->get()
        ]);
    }

    /**
     * Update the specified BAPP Angkut in storage.
     */
    public function update(Request $request, $kode_bapp)
    {
        $bapp = BappAngkut::where('kode_bapp', $kode_bapp)->firstOrFail();
        
        // Add your update logic here
        // For now, we'll just redirect back with a success message
        
        return redirect()->route('bapp.angkut.show', $bapp->id)
            ->with('success', 'BAPP Angkut berhasil diperbarui');
    }

    /**
     * Generate BAPP Angkut code
     * Format: BAPPA-kode_vendor-period-0001 (contoh: BAPPA-VA00001-08-0001)
     */
    private function generateBappAngkutCode($vendorCode, $periodeBapp)
    {
        $prefix = 'BAPPA';
        $periodeFormatted = str_pad($periodeBapp, 2, '0', STR_PAD_LEFT);
        $baseCode = "{$prefix}-{$vendorCode}-{$periodeFormatted}";

        // Find the latest BAPP code with the same base
        $latestBapp = BappAngkut::where('kode_bapp', 'like', $baseCode . '-%')
            ->orderBy('kode_bapp', 'desc')
            ->first();

        if ($latestBapp) {
            // Extract the number part and increment
            $parts = explode('-', $latestBapp->kode_bapp);
            $lastNumber = (int) end($parts);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // First BAPP for this vendor and period
            $nextNumber = '0001';
        }

        return "{$baseCode}-{$nextNumber}";
    }

    /**
     * Hitung pendapatan berdasarkan zonasi
     */
    private function hitungPendapatan($zonasi, $tonaseFinal)
    {
        $hargaPerTon = match(true) {
            str_contains(strtolower($zonasi), '1') => 35000,
            str_contains(strtolower($zonasi), '2') => 42000,
            str_contains(strtolower($zonasi), '3') => 46000,
            str_contains(strtolower($zonasi), '4') => 55000,
            default => 0
        };

        return $tonaseFinal * $hargaPerTon;
    }
}
