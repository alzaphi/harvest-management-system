<?php

namespace App\Http\Controllers;

use App\Models\BappTebang;
use App\Models\HasilTebang;
use App\Models\VendorAngkut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BappTebangController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = BappTebang::with(['vendor', 'hasilTebang']);
        
        // For vendors, only show their own BAPPs
        if ($user->hasRole('vendor')) {
            $query->where('vendor_tebang', $user->vendor_kode);
        }
        
        // For managers, show all BAPPs but with appropriate actions
        $bapps = $query->orderBy('tanggal_bapp', 'desc')->paginate(10);
        
        return view('bapp.index', compact('bapps'));
    }
    
    public function approve(Request $request, $jenis, $id)
    {
        $bapp = BappTebang::findOrFail($id);
        $approvalType = $request->input('approval_type');
        
        if ($approvalType === 'plantation') {
            $bapp->status = 'pending_cdr_approval';
            $message = 'BAPP approved by Plantation Manager';
        } elseif ($approvalType === 'cdr') {
            $bapp->status = 'approved';
            $message = 'BAPP approved by CDR Manager';
        } else {
            return back()->with('error', 'Invalid approval type');
        }
        
        $bapp->save();
        return back()->with('success', $message);
    }

    // STEP 1: Tampilkan hasil tebang yang bisa digenerate
    public function generateSelection($kode_vendor)
    {
        $vendor = VendorAngkut::where('kode_vendor', $kode_vendor)->firstOrFail();
        $hasilTebang = HasilTebang::where('vendor_tebang', $kode_vendor)
            ->where('status', 'Not Generated') // pastikan sama persis dengan DB
            ->orderBy('tanggal_timbang')
            ->get();

        return view('bapp.generate-selection', compact('vendor', 'hasilTebang'));
    }

    // Show confirmation form (GET request)
    public function showConfirm(Request $request)
    {
        // Redirect back if no data is in the session
        if (!session()->has('bapp_data')) {
            return redirect()->route('bapp.index')
                ->with('error', 'Tidak ada data BAPP yang dapat dikonfirmasi. Silakan pilih data terlebih dahulu.');
        }

        // Get data from session
        $data = session('bapp_data');

        return view('bapp.confirm-bapp', [
            'vendor' => $data['vendor'],
            'hasilTebang' => $data['hasilTebang'],
            'totalTonase' => $data['totalTonase'],
            'totalSortase' => $data['totalSortase'],
            'totalTonaseFinal' => $data['totalTonaseFinal'],
            'hasilTebangIds' => $data['hasilTebangIds']
        ]);
    }

    // STEP 2: Konfirmasi sebelum generate (POST request)
    public function confirmBapp(Request $request)
    {
        $request->validate([
            'hasil_tebang_ids' => 'required|array',
            'hasil_tebang_ids.*' => 'exists:hasil_tebang,kode_hasil_tebang',
            'vendor_kode' => 'required|exists:vendor_angkut,kode_vendor',
        ]);

        $hasilTebangIds = $request->input('hasil_tebang_ids');
        $vendor = VendorAngkut::where('kode_vendor', $request->input('vendor_kode'))->firstOrFail();

        $hasilTebang = HasilTebang::whereIn('kode_hasil_tebang', $hasilTebangIds)
            ->where('status', 'Not Generated')
            ->get();

        if ($hasilTebang->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data hasil tebang yang valid untuk diproses.');
        }

        // Perhitungan total
        $totalTonase = $hasilTebang->sum('netto2');
        $totalSortase = $hasilTebang->sum('sortase');
        $totalTonaseFinal = $totalTonase - $totalSortase;

        // Store data in session
        $bappData = [
            'vendor' => $vendor,
            'hasilTebang' => $hasilTebang,
            'totalTonase' => $totalTonase,
            'totalSortase' => $totalSortase,
            'totalTonaseFinal' => $totalTonaseFinal,
            'hasilTebangIds' => $hasilTebangIds
        ];
        
        // Store the data in the session
        session(['bapp_data' => $bappData]);

        // Redirect to the showConfirm method
        return redirect()->route('bapp.confirm.form');
    }

    // Generate BAPP code
    private function generateBappCode($vendorCode, $periodeBapp, $jenis = 'tebang')
    {
        // Format: BAPPT-kode_vendor-period-0001
        $prefix = $jenis === 'tebang' ? 'BAPPT' : 'BAPPA';
        $periodeFormatted = str_pad($periodeBapp, 2, '0', STR_PAD_LEFT);
        $baseCode = "{$prefix}-{$vendorCode}-{$periodeFormatted}";
        
        // Find the latest BAPP code with the same base
        $latestBapp = BappTebang::where('kode_bapp', 'like', $baseCode . '-%')
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

    // STEP 3: Simpan BAPP
    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            // Debug log all request data
            \Log::info('BAPP Store Request Data:', $request->all());
            
            // Decode the JSON string for hasil_tebang_ids if it's a string
            if (is_string($request->hasil_tebang_ids)) {
                $request->merge([
                    'hasil_tebang_ids' => json_decode($request->hasil_tebang_ids, true)
                ]);
            }
            
            $validated = $request->validate([
                'hasil_tebang_ids' => 'required|array',
                'hasil_tebang_ids.*' => 'exists:hasil_tebang,kode_hasil_tebang',
                'vendor_kode' => 'required|exists:vendor_angkut,kode_vendor',
                'periode_bapp' => ['required', 'integer', 'min:1', 'max:12'],
                'tanggal_bapp' => 'required|date',
                'jenis_tebang' => 'required|string',
                'divisi' => 'required|string',
                'kode_petak' => 'required|string',
                'tonase' => 'required|numeric',
                'sortase' => 'required|numeric',
                'tonase_final' => 'required|numeric',
                'sewa_grab' => 'array',
                'sewa_grab.*' => 'nullable|numeric',
                'total_pendapatan' => 'array',
                'total_pendapatan.*' => 'required|numeric',
            ]);
            
            // Debug log after validation
            \Log::info('BAPP Store Validation Passed');

            // Generate BAPP code
            $kodeBapp = $this->generateBappCode(
                $request->vendor_kode,
                $request->periode_bapp,
                'tebang' // or 'angkut' for BAPP Angkut
            );

            $sewaGrabArray = $request->sewa_grab ?? [];
            $totalPendapatanArray = $request->total_pendapatan ?? [];

            foreach ($request->hasil_tebang_ids as $index => $kodeHasil) {
                $hasil = HasilTebang::where('kode_hasil_tebang', $kodeHasil)->firstOrFail();
                $tonaseFinal = $hasil->netto2;
                $sewaGrab = $sewaGrabArray[$index] ?? 0;
                $totalPendapatan = $totalPendapatanArray[$index] ?? 0;

                $bappData = [
                    'kode_bapp' => $kodeBapp,
                    'kode_hasil_tebang' => $hasil->kode_hasil_tebang,
                    'vendor_tebang' => $request->vendor_kode,
                    'periode_bapp' => (int)$request->periode_bapp, // Ubah ke integer tanpa padding
                    'tanggal_bapp' => $request->tanggal_bapp,
                    'jenis_tebang' => $request->jenis_tebang,
                    'divisi' => $request->divisi,
                    'kode_petak' => $request->kode_petak,
                    'tonase' => $hasil->netto1,
                    'sortase' => $hasil->sortase,
                    'tonase_final' => $tonaseFinal,
                    'tebang' => $tonaseFinal * 54000,
                    'ikat_tumpuk' => $tonaseFinal * 15000,
                    'muat' => $tonaseFinal * 54000,
                    'sewa_grab' => $sewaGrab,
                    'insentif_pasok' => $tonaseFinal * 9000,
                    'insentif_beras_tk' => $tonaseFinal * 6000,
                    'total_pendapatan' => $totalPendapatan,
                    'diajukan_oleh' => null,
                    'ttd_diajukan_oleh_path' => null,
                    'diperiksa_oleh' => null,
                    'ttd_diperiksa_oleh_path' => null,
                    'disetujui_oleh' => null,
                    'ttd_disetujui_oleh_path' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $bapp = BappTebang::create($bappData);

                if (!$bapp) {
                    throw new \Exception('Gagal menyimpan data BAPP ke database');
                }

                // Update status hasil tebang
                $hasil->status = 'Generated';
                if (!$hasil->save()) {
                    throw new \Exception('Gagal memperbarui status hasil tebang');
                }
            }

            // Clear the session data
            session()->forget('bapp_data');
            
            \DB::commit();
            return redirect()->route('bapp.index')
                ->with('success', 'BAPP Tebang berhasil dibuat dengan nomor: ' . $kodeBapp);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating BAPP: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat BAPP: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified BAPP.
     */
    public function show($jenis, $kodeBapp)
    {
        try {
            // Get the first BAPP item to check vendor and basic info
            $bapp = BappTebang::where('kode_bapp', $kodeBapp)
                ->firstOrFail();

            // Get all BAPP items with the same code
            $bappItems = BappTebang::where('kode_bapp', $kodeBapp)
                ->with(['hasilTebang', 'vendor']) // Ensure vendor relationship is loaded
                ->get();

            // If no items found, throw exception
            if ($bappItems->isEmpty()) {
                throw new \Exception('Data BAPP tidak ditemukan');
            }

            // Get vendor from the first item
            $vendor = $bappItems->first()->vendor;
            
            // Calculate totals
            $totalTonase = $bappItems->sum('tonase');
            $totalSortase = $bappItems->sum('sortase');
            $totalTonaseFinal = $bappItems->sum('tonase_final');

            return view('bapp.show', [
                'bapp' => (object)[
                    'kode_bapp' => $kodeBapp,
                    'periode_bapp' => $bapp->periode_bapp,
                    'tanggal_bapp' => $bapp->tanggal_bapp,
                    'vendor' => $vendor ? (object)[
                        'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        'kode_vendor' => $vendor->kode_vendor ?? '',
                    ] : (object)[
                        'nama_vendor' => 'Vendor Tidak Ditemukan',
                        'kode_vendor' => '',
                    ],
                ],
                'bappItems' => $bappItems,
                'totalTonase' => $totalTonase,
                'totalSortase' => $totalSortase,
                'totalTonaseFinal' => $totalTonaseFinal,
                'jenis' => $jenis
            ]);

        } catch (\Exception $e) {
            \Log::error('Error showing BAPP ' . $kodeBapp . ': ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('bapp.index')
                ->with('error', 'Data BAPP tidak ditemukan: ' . $e->getMessage());
        }
    }
}
