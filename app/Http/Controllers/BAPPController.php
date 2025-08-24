<?php

namespace App\Http\Controllers;

use App\Models\BappTebang;
use App\Models\BappAngkut;
use App\Models\ComplainBappTebang;
use App\Models\VendorAngkut;
use App\Models\VendorTebang;
use App\Models\HasilTebang;
use App\Models\Spd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BAPPController extends Controller
{
    /**
     * Menampilkan daftar BAPP Tebang.
     */
    /**
     * Menampilkan daftar BAPP berdasarkan jenis (tebang/angkut)
     *
     * @param string $jenis Jenis BAPP (tebang/angkut)
     * @return \Illuminate\View\View
     */
    /**
     * Display a listing of BAPP based on type (tebang/angkut)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $search = request('search');
        $jenis = request('jenis', 'tebang');
        $user = auth()->user();

        // Get SPDs waiting for approval
        $spds = Spd::whereIn('status', ['Diajukan', 'Diperiksa'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($jenis === 'tebang') {
            $query = BappTebang::query()
                ->select(
                    'bapp_tebang.kode_bapp',
                    'bapp_tebang.tanggal_bapp',
                    'bapp_tebang.periode_bapp',
                    'bapp_tebang.vendor_tebang as vendor_code',
                    'bapp_tebang.total_pendapatan',
                    'bapp_tebang.status',
                    DB::raw('"tebang" as jenis')
                )
                ->leftJoin('complainbapptebang', 'complainbapptebang.kode_bapp', '=', 'bapp_tebang.kode_bapp')
                ->selectRaw('GROUP_CONCAT(DISTINCT complainbapptebang.deskripsi SEPARATOR "|||") as deskripsi_komplain')
                ->groupBy('bapp_tebang.kode_bapp', 'bapp_tebang.tanggal_bapp', 'bapp_tebang.periode_bapp', 
                         'bapp_tebang.vendor_tebang', 'bapp_tebang.total_pendapatan', 'bapp_tebang.status');
        } else {
            $query = BappAngkut::query()
                ->select(
                    'bapp_angkut.kode_bapp',
                    'bapp_angkut.tanggal_bapp',
                    'bapp_angkut.periode_bapp',
                    'bapp_angkut.vendor_angkut as vendor_code',
                    'bapp_angkut.total_pendapatan',
                    'bapp_angkut.status',
                    DB::raw('"angkut" as jenis')
                )
                ->groupBy('bapp_angkut.kode_bapp', 'bapp_angkut.tanggal_bapp', 'bapp_angkut.periode_bapp', 
                         'bapp_angkut.vendor_angkut', 'bapp_angkut.total_pendapatan', 'bapp_angkut.status');
        }

        // Apply search filter
        // Filter by vendor if user has vendor role
        if ($user && $user->hasRole('vendor')) {
            $vendorTable = $jenis === 'tebang' ? 'vendor_tebang' : 'vendor_angkut';
            $query->whereHas('vendor', function($q) use ($user) {
                $q->where('nama_vendor', $user->name);
            });
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_bapp', 'like', "%{$search}%")
                  ->orWhere('periode_bapp', 'like', "%{$search}%");
            });
        }

        // Apply role-based filters
        $user = auth()->user();
        if ($user->hasRole('vendor')) {
            $query->whereIn('status', ['Diajukan', 'Diperiksa', 'Disetujui']);
        } elseif ($user->hasRole('Assistant Finance') || $user->hasRole('Manager Finance')) {
            $query->whereIn('status', ['Diperiksa', 'Diajukan', 'Disetujui']);
        } elseif ($user->hasRole('Manager Plantation')) {
            $query->whereIn('status', ['Diperiksa', 'Disetujui']);
        } elseif ($user->hasRole('Assistant Manager CDR') || $user->hasRole('Manager CDR')) {
            $query->where('status', 'Disetujui');
        }

        $bapps = $query->orderBy('tanggal_bapp', 'desc')->paginate(10);

        // Get vendors for the dropdown
        $vendors = $jenis === 'tebang' 
            ? VendorTebang::orderBy('nama_vendor')->get()
            : VendorAngkut::orderBy('nama_vendor')->get();

        // Transform the collection to include vendor relationships
        $bapps->getCollection()->transform(function($item) use ($jenis) {
            $model = $jenis === 'tebang'
                ? BappTebang::where('kode_bapp', $item->kode_bapp)
                    ->where('tanggal_bapp', $item->tanggal_bapp)
                    ->first()
                : BappAngkut::where('kode_bapp', $item->kode_bapp)
                    ->where('tanggal_bapp', $item->tanggal_bapp)
                    ->first();

            $item->vendor = $model ? $model->vendor : null;
            $item->id = $model ? $model->id : null;
            
            return $item;
        });

        return view('bapp.index', [
            'bapps' => $bapps,
            'jenis' => $jenis,
            'search' => $search,
            'vendors' => $vendors,
            'spds' => $spds
        ]);
    }

    /**
     * Menampilkan detail BAPP.
     */
    public function show($jenis, $id)
    {
        if ($jenis === 'tebang') {
            $bapp = BappTebang::with(['vendor', 'hasilTebang', 'spd.sopir', 'komplain'])->findOrFail($id);
            return view('bapp.show', compact('bapp', 'jenis'));
        } else {
            $bapp = BappAngkut::with([
                'vendor',
                'hasilTebang',
                'spd.sopir',
                'komplain' => function($query) {
                    $query->orderBy('tanggal', 'desc');
                }
            ])->findOrFail($id);

            return view('bapp.showangkut', compact('bapp', 'jenis'));
        }
    }

    /**
     * Show the form for editing the specified BAPP's komplain.
     */
    public function editKomplain($jenis, $kode_bapp)
    {
        if ($jenis === 'tebang') {
            $bapp = BappTebang::with([
                'komplain' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'vendor',
                'hasilTebang',
                'spd.sopir'
            ])
            ->where('kode_bapp', $kode_bapp)
            ->firstOrFail();
        } else {
            // For BAPP Angkut, load all necessary relationships
            $bapp = BappAngkut::with([
                'komplain' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'vendor',
                'hasilTebang' => function($query) {
                    $query->with([
                        'vendorTebang',
                        'vendorAngkut',
                        'subBlock'
                    ]);
                },
                'spd' => function($query) {
                    $query->with('sopir');
                }
            ])
            ->where('kode_bapp', $kode_bapp)
            ->firstOrFail();

            // Debug the loaded data
            \Log::info('BAPP Angkut Data Loaded:', [
                'kode_bapp' => $bapp->kode_bapp,
                'vendor' => $bapp->vendor ? $bapp->vendor->toArray() : null,
                'hasil_tebang' => $bapp->hasilTebang ? $bapp->hasilTebang->toArray() : null,
                'komplain_count' => $bapp->komplain ? $bapp->komplain->count() : 0
            ]);
        }

        // Debug the loaded data
        \Log::info('BAPP Data Loaded:', [
            'kode_bapp' => $bapp->kode_bapp,
            'komplain_count' => $bapp->komplain ? $bapp->komplain->count() : 0,
            'vendor' => $bapp->vendor ? $bapp->vendor->toArray() : null
        ]);

        $view = $jenis === 'tebang' ? 'bapp.edit' : 'bapp.editangkut';
        return view($view, compact('bapp', 'jenis'));
    }

    /**
     * Update the specified komplain in storage.
     */
    public function updateKomplain(Request $request, $jenis, $kode_bapp)
    {
        $request->validate([
            'komplain_id' => 'required|array',
            'komplain' => 'required|array',
            'komplain.*' => 'required|string|max:1000',
        ]);

        // Determine the BAPP type and get the appropriate model
        if ($jenis === 'tebang') {
            $bapp = BappTebang::where('kode_bapp', $kode_bapp)->first();
            $komplainModel = '\App\Models\ComplainBappTebang';
        } else {
            $bapp = BappAngkut::where('kode_bapp', $kode_bapp)->first();
            $komplainModel = '\App\Models\ComplainBappAngkut';
        }

        if (!$bapp) {
            return back()->with('error', 'BAPP tidak ditemukan')->withInput();
        }

        DB::beginTransaction();
        try {
            // Get existing komplain IDs for this BAPP
            $existingKomplainIds = $komplainModel::where('kode_bapp', $kode_bapp)
                ->pluck('complain_id')
                ->toArray();

            $processedIds = [];

            foreach ($request->komplain as $index => $deskripsi) {
                $komplainId = $request->komplain_id[$index] ?? null;

                // Skip if komplain is empty
                if (empty(trim($deskripsi))) {
                    continue;
                }

                $komplainData = [
                    'deskripsi' => $deskripsi,
                    'tanggal' => now(),
                ];

                if (empty($komplainId) || $komplainId === 'new') {
                    // Create new komplain
                    $komplainData['kode_bapp'] = $bapp->kode_bapp;
                    $komplainData['created_by'] = auth()->id();

                    $komplain = $komplainModel::create($komplainData);
                    $processedIds[] = $komplain->complain_id;
                } else {
                    // Update existing komplain
                    $komplain = $komplainModel::where('complain_id', $komplainId)
                        ->where('kode_bapp', $bapp->kode_bapp)
                        ->first();

                    if (!$komplain) {
                        continue; // Skip if komplain not found
                    }

                    $komplain->update($komplainData);
                    $processedIds[] = $komplain->complain_id;
                }
            }

            // Delete any komplains that were not in the form submission
            if (!empty($existingKomplainIds)) {
                $toDelete = array_diff($existingKomplainIds, $processedIds);
                if (!empty($toDelete)) {
                    $komplainModel::whereIn('complain_id', $toDelete)
                        ->where('kode_bapp', $kode_bapp)
                        ->delete();
                }
            }

            DB::commit();
            return redirect()->route('bapp.index', ['jenis' => $jenis])
                ->with('success', 'Komplain berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating komplain:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
                'kode_bapp' => $bapp->kode_bapp ?? null
            ]);
            return back()->with('error', 'Gagal memperbarui komplain: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit BAPP.
     */
    public function edit($jenis, $id)
    {
        if ($jenis === 'tebang') {
            $bapp = BappTebang::findOrFail($id);
            $vendors = VendorTebang::all();
        } else {
            $bapp = BappAngkut::findOrFail($id);
            $vendors = VendorAngkut::all();
        }

        return view('bapp.edit', compact('bapp', 'vendors', 'jenis'));
    }

    /**
     * Update the specified BAPP in storage.
     */
    public function update(Request $request, $jenis, $id)
    {
        if ($jenis === 'tebang') {
            $bapp = BappTebang::findOrFail($id);
        } else {
            $bapp = BappAngkut::findOrFail($id);
        }

        $validated = $request->validate([
            'kode_bapp' => 'required|string|max:50',
            'tanggal_bapp' => 'required|date',
            'vendor_id' => 'required|exists:'.($jenis === 'tebang' ? 'vendor_tebang' : 'vendor_angkut').',id',
            // Add other validation rules as needed
        ]);

        $bapp->update($validated);

        return redirect()->route('bapp.index', ['jenis' => $jenis])
                        ->with('success', 'BAPP berhasil diperbarui');
    }

    /**
     * Remove the specified BAPP from storage.
     */
    public function destroy($jenis, $id)
    {
        if ($jenis === 'tebang') {
            $bapp = BappTebang::findOrFail($id);
        } else {
            $bapp = BappAngkut::findOrFail($id);
        }

        $bapp->delete();

        return redirect()->route('bapp.index', ['jenis' => $jenis])
                        ->with('success', 'BAPP berhasil dihapus');
    }

    public function recap(Request $request)
{
    // Get all SPDs first to check statuses
    $spdStatuses = Spd::pluck('status', 'periode');

    // Query BAPP Tebang
    $tebangQuery = BappTebang::select(
            'periode_bapp',
            DB::raw('MIN(tanggal_bapp) as min_date'),
            DB::raw('COUNT(DISTINCT vendor_tebang) as total_vendors'),
            DB::raw('SUM(total_pendapatan) as total_amount')
        );

    // Query BAPP Angkut
    $angkutQuery = BappAngkut::select(
            'periode_bapp',
            DB::raw('MIN(tanggal_bapp) as min_date'),
            DB::raw('COUNT(DISTINCT vendor_angkut) as total_vendors'),
            DB::raw('SUM(total_pendapatan) as total_amount')
        );

    // ===== Tambahkan Filter di sini =====
    if ($request->filled('period')) {
        $tebangQuery->where('periode_bapp', $request->period);
        $angkutQuery->where('periode_bapp', $request->period);
    }

    if ($request->filled('month')) {
        $tebangQuery->whereMonth('tanggal_bapp', $request->month);
        $angkutQuery->whereMonth('tanggal_bapp', $request->month);
    }

    if ($request->filled('status')) {
        // Filter berdasarkan status SPD (mapping ke periode)
        $periodeByStatus = $spdStatuses->filter(fn($status) => strtolower($status) === strtolower($request->status))->keys();
        $tebangQuery->whereIn('periode_bapp', $periodeByStatus);
        $angkutQuery->whereIn('periode_bapp', $periodeByStatus);
    }
    // =====================================

    $tebangQuery->groupBy('periode_bapp');
    $angkutQuery->groupBy('periode_bapp');

    // Gabungkan kedua query
    $query = $tebangQuery->unionAll($angkutQuery);

    // Ambil data dan gabungkan berdasarkan periode
    $periods = DB::table(DB::raw("({$query->toSql()}) as combined"))
        ->mergeBindings($query->getQuery())
        ->select(
            'periode_bapp',
            DB::raw('MIN(min_date) as min_date'),
            DB::raw('SUM(total_vendors) as total_vendors'),
            DB::raw('SUM(total_amount) as total_amount')
        )
        ->groupBy('periode_bapp')
        ->orderBy('periode_bapp', 'desc');

    // Get all results first
    $results = $periods->get();

    // Transform and filter the items
    $transformedItems = $results->map(function($item) use ($spdStatuses) {
        $date = Carbon::parse($item->min_date);
        $status = $spdStatuses[$item->periode_bapp] ?? 'draft';

        return [
            'period' => $item->periode_bapp,
            'month' => $date->isoFormat('MMMM YYYY'),
            'total_vendors' => $item->total_vendors,
            'total_amount' => $item->total_amount,
            'year' => $date->year,
            'status' => $status,
            'min_date' => $item->min_date, // Keep this for sorting
            'total_vendors_raw' => $item->total_vendors, // Keep original for sorting
            'total_amount_raw' => $item->total_amount // Keep original for sorting
        ];
    });

    // Filter out draft status only for Finance users
    if (auth()->user()->hasRole('Assistant Finance') || auth()->user()->hasRole('Manager Finance')) {
        // First filter the spdStatuses to exclude draft status
        $filteredSpdStatuses = $spdStatuses->filter(function($status) {
            return $status !== 'draft';
        });
        
        // Then filter the items based on the filtered statuses
        $transformedItems = $transformedItems->filter(function($item) use ($filteredSpdStatuses) {
            // If the period is not in the filtered statuses, it means it was filtered out
            return $filteredSpdStatuses->has($item['period']);
        });
    }

    // Apply sorting
    $transformedItems = $transformedItems->sortByDesc('period');

    // Create pagination manually
    $page = request()->get('page', 1);
    $perPage = 15;
    $currentPageItems = $transformedItems->forPage($page, $perPage);

    // Create a new paginator with the filtered items
    $periods = new \Illuminate\Pagination\LengthAwarePaginator(
        $currentPageItems,
        $transformedItems->count(),
        $perPage,
        $page,
        ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
    );

    // Data untuk dropdown
    $allPeriods = BappTebang::select('periode_bapp')
        ->union(BappAngkut::select('periode_bapp'))
        ->distinct()
        ->orderBy('periode_bapp', 'desc')
        ->pluck('periode_bapp');

    return view('rekap-bapp.index', compact('periods', 'allPeriods'));
}


    /**
     * Show BAPP recap detail for a specific period
     */
    public function recapDetail($period)
    {
        // Get all BAPP Tebang data for the period grouped by vendor
        $vendorsTebang = BappTebang::with('vendor')
            ->where('periode_bapp', $period)
            ->selectRaw('vendor_tebang as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_tebang')
            ->get()
            ->map(function($item) use ($period) {
                $vendor = $item->vendor ?? null;
                return [
                    'vendor' => (object)[
                        'kode_vendor' => $vendor->kode_vendor ?? $item->vendor_code,
                        'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        'jenis_vendor' => $vendor->jenis_vendor ?? 'Tebang'
                    ],
                    'total_pendapatan' => $item->total_pendapatan,
                    'total_tonase' => $item->total_tonase,
                    'type' => 'tebang'
                ];
            });

        // Get all BAPP Angkut data for the period grouped by vendor
        $vendorsAngkut = BappAngkut::with('vendor')
            ->where('periode_bapp', $period)
            ->selectRaw('vendor_angkut as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_angkut')
            ->get()
            ->map(function($item) use ($period) {
                $vendor = $item->vendor ?? null;
                return [
                    'vendor' => (object)[
                        'kode_vendor' => $vendor->kode_vendor ?? $item->vendor_code,
                        'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                        'jenis_vendor' => $vendor->jenis_vendor ?? 'Angkut'
                    ],
                    'total_pendapatan' => $item->total_pendapatan,
                    'total_tonase' => $item->total_tonase,
                    'type' => 'angkut'
                ];
            });

        // Gabungkan data tebang dan angkut
        $vendors = $vendorsTebang->merge($vendorsAngkut);

        $periodName = Carbon::createFromFormat('m', $period)->format('F Y');
        $grandTotalTonase = $vendors->sum('total_tonase');
        $grandTotalPendapatan = $vendors->sum('total_pendapatan');

        // Check if SPD exists for this period
        $spd = Spd::where('periode', $period)->first();

        return view('rekap-bapp.show', [
            'vendors' => $vendors,
            'period' => $period,
            'periodName' => $periodName,
            'totalAmount' => $grandTotalPendapatan,
            'grandTotalTonase' => $grandTotalTonase,
            'spdStatus' => $spd ? $spd->status : null,
            'spd' => $spd
        ]);
    }

    /**
     * Show SPD (Surat Permintaan Dana) for all vendors in the period
     */
    public function showSPD($period)
    {
        // Hitung grand total dari BAPP Tebang
        $vendorsTebang = BappTebang::with('vendor')
            ->where('periode_bapp', $period)
            ->selectRaw('vendor_tebang as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_tebang')
            ->get();

        // Hitung grand total dari BAPP Angkut
        $vendorsAngkut = BappAngkut::with('vendor')
            ->where('periode_bapp', $period)
            ->selectRaw('vendor_angkut as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_angkut')
            ->get();

        // Gabungkan dan hitung total pendapatan dan tonase
        $grandTotalPendapatan = $vendorsTebang->sum('total_pendapatan') + $vendorsAngkut->sum('total_pendapatan');
        $grandTotalTonase = $vendorsTebang->sum('total_tonase') + $vendorsAngkut->sum('total_tonase');

        if ($vendorsTebang->isEmpty() && $vendorsAngkut->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data BAPP untuk periode ini.');
        }

        // Format data SPD
        $documentNumber = now()->format('Ym') . '-' . $period;
        $tanggalPengajuan = now()->format('Y-m-d');

        // Gunakan periode yang ada dari BAPP Tebang atau BAPP Angkut
        $firstBapp = BappTebang::where('periode_bapp', $period)->first();
        if (!$firstBapp) {
            $firstBapp = BappAngkut::where('periode_bapp', $period)->first();
        }

        $periodeBapp = $firstBapp ? $firstBapp->periode_bapp : $period;
        $terbilang = $this->terbilang($grandTotalPendapatan) . ' Rupiah';

        // Cek apakah SPD untuk periode ini sudah ada
        $spd = Spd::updateOrCreate(
            ['periode' => $periodeBapp],
            [
                'no_spd' => $documentNumber,
                'tanggal_spd' => $tanggalPengajuan,
                'total_dana' => $grandTotalPendapatan,
                'status' => $spd->status ?? 'Draft', // Jangan timpa status jika sudah ada
                'diajukan_oleh' => auth()->user()->name ?? ''
            ]
        );

        return view('rekap-bapp.spd', compact(
            'spd', 'period', 'periodeBapp', 'documentNumber',
            'tanggalPengajuan', 'grandTotalPendapatan',
            'grandTotalTonase', 'terbilang'
        ));
    }

    /**
     * Display the specified SPD.
     */
    public function viewSPD($id)
    {
        $spd = Spd::with([
            'diajukanOleh',
            'diverifikasiOleh',
            'disetujuiOleh',
            'dibayarOleh',
            'ditolakOleh',
            'diketahuiOleh'
        ])->findOrFail($id);

        // Generate terbilang for the total amount
        $terbilang = $this->terbilang($spd->total_dana) . ' Rupiah';

        // Get the period for navigation
        $periodeBapp = $spd->periode;

        return view('rekap-bapp.spd', compact('spd', 'terbilang', 'periodeBapp'));
    }

    /**
     * Handle SPD signing
     */
    public function signSPD(Request $request, $id)
    {
        $request->validate([
            'signature_type' => 'required|string|in:diajukan_oleh,diverifikasi_oleh,diketahui_oleh,disetujui_oleh,dibayar_oleh',
            'signature_data' => 'required|string',
        ]);

        $spd = Spd::findOrFail($id);
        $user = auth()->user();
        $now = now();

        // Handle the signature data
        $signature = $request->signature_data;
        $signaturePath = '';

        if ($signature) {
            // Save the signature image
            $image = str_replace('data:image/png;base64,', '', $signature);
            $image = str_replace(' ', '+', $image);
            $imageName = 'signature_' . $user->id . '_' . time() . '.png';
            $path = 'signatures/' . $imageName;

            // Ensure the directory exists
            if (!file_exists(public_path('storage/signatures'))) {
                mkdir(public_path('storage/signatures'), 0755, true);
            }

            file_put_contents(public_path('storage/' . $path), base64_decode($image));
            $signaturePath = $path;
        }

        // Update the SPD with the signature and user info
        $signatureField = 'ttd_' . $request->signature_type;
        $userField = $request->signature_type; // e.g., 'diajukan_oleh'
        $userFieldId = $request->signature_type . '_id'; // e.g., 'diajukan_oleh_id'

        $updateData = [
            $signatureField => $signaturePath,
            $userField => $user->name, // Simpan nama user yang login
            $userFieldId => $user->id, // Simpan ID user yang login
        ];

        // Jika ini adalah tanda tangan pertama, set status ke 'Diajukan'
        if ($spd->status === 'Draft' && $request->signature_type === 'diajukan_oleh') {
            $updateData['status'] = 'Diajukan';
        }

        $spd->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan berhasil disimpan.',
            'redirect' => route('spd.index')
        ]);
    }

    /**
     * Submit SPD for review (change status from Draft to Diajukan)
     */
    public function submitForReview($id)
    {
        try {
            $spd = Spd::findOrFail($id);
            $user = auth()->user();

            // Validasi: Pastikan status masih Draft dan tanda tangan sudah ada
            if ($spd->status !== 'Draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'SPD tidak dapat diajukan karena status sudah ' . $spd->status
                ], 422);
            }

            if (empty($spd->ttd_diajukan_oleh)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanda tangan "Diajukan Oleh" belum diisi'
                ], 422);
            }

            // Update status dan tambahkan timestamp
            $spd->update([
                'status' => 'Diajukan',
                'submitted_at' => now(),
                'submitted_by' => $user->name,
                'diajukan_oleh' => $user->name, // Pastikan nama pengaju juga diupdate
                'diajukan_oleh_id' => $user->name // Pastikan ID pengaju juga diupdate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SPD berhasil diajukan untuk diperiksa',
                'redirect' => route('spd.index')
            ]);

        } catch (\Exception $e) {
            \Log::error('Submit SPD Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan SPD: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified SPD from storage.
     */
    public function destroySPD($id)
    {
        $spd = Spd::findOrFail($id);
        $spd->delete();

        return redirect()->route('bapp.approval.index')
                        ->with('success', 'SPD berhasil dihapus');
    }

    /**
     * Complete payment for an SPD (change status from Disetujui to Dibayar)
     */
    public function completePaymentSPD(Request $request, $id)
    {
        $request->validate([
            'payment_date' => 'required|date',
        ]);

        $spd = Spd::findOrFail($id);

        // Check if current status is Disetujui
        if ($spd->status !== 'Disetujui') {
            return back()->with('error', 'Hanya SPD dengan status "Disetujui" yang dapat ditandai sebagai dibayar');
        }

        // Check if user has permission to process payment
        if (!auth()->user()->can('process-payment')) {
            abort(403, 'Anda tidak memiliki izin untuk memproses pembayaran');
        }

        $spd->update([
            'status' => 'Dibayar',
            'dibayar_oleh' => auth()->id(),
            'dibayar_pada' => $request->payment_date,
        ]);

        return back()->with('success', 'Status pembayaran SPD berhasil diperbarui');
    }

    /**
     * Reject an SPD
     */
    public function rejectSPD(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ]);

        $spd = Spd::findOrFail($id);
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Check if the SPD can be rejected
        if (!in_array($spd->status, ['Diajukan', 'Diverifikasi', 'Disetujui', 'Selesai']) && !$isAdmin) {
            return back()->with('error', 'SPD tidak dapat ditolak pada status saat ini.');
        }

        // Check permission
        if (!$user->can('reject-spd') && !$isAdmin) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menolak SPD.');
        }

        // Update the SPD status and rejection reason
        $spd->update([
            'status' => 'Ditolak',
            'ditolak_oleh' => $user->id,
            'alasan_penolakan' => $request->alasan_penolakan,
            'ditolak_pada' => now(),
        ]);

        return redirect()->route('bapp.approval.index')
            ->with('success', 'SPD berhasil ditolak.');
    }

    /**
     * Verify an SPD (change status from Diajukan to Diverifikasi)
     */
    public function verifySPD($id)
    {
        $spd = Spd::findOrFail($id);

        // Check if current status is Diajukan
        if ($spd->status !== 'Diajukan') {
            return back()->with('error', 'Hanya SPD dengan status "Diajukan" yang dapat diverifikasi');
        }

        // Check if user has permission to verify
        if (!auth()->user()->can('verify-spd')) {
            abort(403, 'Anda tidak memiliki izin untuk memverifikasi SPD');
        }

        $spd->update([
            'status' => 'Diverifikasi',
            'diverifikasi_oleh' => auth()->id(),
            'diverifikasi_pada' => now(),
        ]);

        return back()->with('success', 'SPD berhasil diverifikasi');
    }

    /**
     * Approve an SPD (change status from Diverifikasi to Disetujui)
     */
    public function approveSPD($id)
    {
        $spd = Spd::findOrFail($id);

        // Check if current status is Diverifikasi
        if ($spd->status !== 'Diverifikasi') {
            return back()->with('error', 'Hanya SPD dengan status "Diverifikasi" yang dapat disetujui');
        }

        // Check if user has permission to approve
        if (!auth()->user()->can('approve-spd')) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui SPD');
        }

        $spd->update([
            'status' => 'Disetujui',
            'disetujui_oleh' => auth()->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('success', 'SPD berhasil disetujui');
    }


    // AKSES APPROVAL SPD
    /**
     * Display a listing of SPDs for approval
     */
    public function listSPD()
    {
        $user = auth()->user();

        // Query for SPDs that need approval based on user role
        $query = Spd::query();

        // Admin can see all data regardless of status
        if ($user->hasRole('Admin') || $user->hasRole('admin')) {
            // No status filter for admin, they see everything
        } 
        // QA can only see 'Diajukan' status
        elseif ($user->hasRole('QA')) {
            $query->where('status', 'Diajukan');
        }
        // Manager CDR can only see 'Diperiksa' status
        elseif ($user->hasRole('Manager CDR')) {
            $query->where('status', 'Diperiksa');
        }
        // Director can only see 'Diverifikasi' status
        elseif ($user->hasRole('Director')) {
            $query->where('status', 'Diverifikasi');
        }
        // Manager Finance can only see 'Disetujui' status
        elseif ($user->hasRole('Manager Finance')) {
            $query->where('status', 'Disetujui');
        }
        // For any other role, show no data
        else {
            $query->where('id', 0);
        }

        $spds = $query->with(['diajukanOleh', 'diverifikasiOleh', 'disetujuiOleh', 'dibayarOleh'])
                     ->orderBy('created_at', 'desc')
                     ->paginate(10);

        return view('rekap-bapp.approval.index', compact('spds'));
    }

    /**
     * Show the SPD approval form
     */
    public function showApproval($id)
    {
        $spd = Spd::with([
            'diajukanOleh',
            'diverifikasiOleh',
            'disetujuiOleh',
            'dibayarOleh',
            'ditolakOleh',
            'diketahuiOleh'
        ])->findOrFail($id);

        // Generate terbilang for the total amount
        $terbilang = $this->terbilang($spd->total_dana) . ' Rupiah';

        $user = auth()->user();
        $canApprove = false;
        $canVerify = false;
        $canProcessPayment = false;
        $nextStep = '';

        // Check permissions based on status and user role
        if ($user->hasRole('Admin')) {
            if ($spd->status === 'Diajukan') {
                $canVerify = true;
                $nextStep = 'Verifikasi';
            } elseif ($spd->status === 'Diverifikasi') {
                $canApprove = true;
                $nextStep = 'Setujui';
            }
        } elseif ($user->hasRole('Finance') && $spd->status === 'Disetujui') {
            $canProcessPayment = true;
            $nextStep = 'Proses Pembayaran';
        } elseif ($user->hasRole('Manager') && in_array($spd->status, ['Diajukan', 'Diperiksa'])) {
            $nextStep = 'Periksa';
        }

        return view('rekap-bapp.approval.show', compact(
            'spd', 
            'terbilang', 
            'canApprove', 
            'canVerify', 
            'canProcessPayment',
            'nextStep'
        ));
    }

    /**
     * Process SPD approval with digital signature
     */
    public function processApproval(Request $request, $id)
    {
        try {
            $request->validate([
                'signature_data' => 'required|string',
                'status' => 'required|string|in:Diperiksa,Diverifikasi,Disetujui,Selesai',
            ]);

            $spd = Spd::findOrFail($id);
            $user = auth()->user();
            $now = now();
            
            // Validate current status and transition
            $currentStatus = $spd->status;
            $nextStatus = $request->status;
            
            // Define allowed status transitions
            $allowedTransitions = [
                'Diajukan' => ['Diperiksa'],
                'Diperiksa' => ['Diverifikasi'],
                'Diverifikasi' => ['Disetujui'],
                'Disetujui' => ['Selesai']
            ];
            
            // Validate status transition
            if (!isset($allowedTransitions[$currentStatus]) || 
                !in_array($nextStatus, $allowedTransitions[$currentStatus])) {
                return back()->with('error', 'Transisi status tidak valid: ' . $currentStatus . ' ke ' . $nextStatus);
            }

            // Handle signature
            $signatureData = $request->signature_data;
            $signaturePath = '';
            
            if ($signatureData) {
                // Save the signature image
                $image = str_replace('data:image/png;base64,', '', $signatureData);
                $image = str_replace(' ', '+', $image);
                $imageName = 'signature_' . $user->id . '_' . time() . '.png';
                $path = 'signatures/' . $imageName;

                // Ensure the directory exists
                if (!file_exists(public_path('storage/signatures'))) {
                    mkdir(public_path('storage/signatures'), 0755, true);
                }

                file_put_contents(public_path('storage/' . $path), base64_decode($image));
                $signaturePath = $path;
            }

            // Define update data based on next status
            $updates = [];
            $successMessage = '';
            
            switch ($nextStatus) {
                case 'Diperiksa':
                    $updates = [
                        'ttd_diverifikasi_oleh' => $signaturePath,
                        'diverifikasi_oleh' => $user->id,
                        'diverifikasi_pada' => $now,
                        'status' => 'Diperiksa'
                    ];
                    $successMessage = 'SPD berhasil diperiksa';
                    break;
                    
                case 'Diverifikasi':
                    $updates = [
                        'ttd_diketahui_oleh' => $signaturePath,
                        'diketahui_oleh' => $user->id,
                        'diketahui_pada' => $now,
                        'status' => 'Diverifikasi'
                    ];
                    $successMessage = 'SPD berhasil diverifikasi';
                    break;
                    
                case 'Disetujui':
                    $updates = [
                        'ttd_disetujui_oleh' => $signaturePath,
                        'disetujui_oleh' => $user->id,
                        'disetujui_pada' => $now,
                        'status' => 'Disetujui'
                    ];
                    $successMessage = 'SPD berhasil disetujui';
                    break;
                    
                case 'Selesai':
                    $updates = [
                        'ttd_dibayar_oleh' => $signaturePath,
                        'dibayar_oleh' => $user->id,
                        'dibayar_pada' => $now,
                        'status' => 'Selesai'
                    ];
                    $successMessage = 'Pembayaran SPD berhasil dikonfirmasi';
                    break;
            }
            
            // Update SPD
            $spd->update($updates);
            
            return redirect()->route('spd.index')
                ->with('success', $successMessage);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Process Approval Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Convert number to words in Indonesian.
     *
     * @param  int  $number
     * @return string
     */
    private function terbilang($number)
    {
        $number = abs($number);
        $words = array(
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        );

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return $this->terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            return $this->terbilang($number / 10) . ' puluh ' . $this->terbilang($number % 10);
        } elseif ($number < 200) {
            return 'seratus ' . $this->terbilang($number - 100);
        } elseif ($number < 1000) {
            return $this->terbilang($number / 100) . ' ratus ' . $this->terbilang($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . $this->terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return $this->terbilang($number / 1000) . ' ribu ' . $this->terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->terbilang($number / 1000000) . ' juta ' . $this->terbilang($number % 1000000);
        } else {
            return 'Angka terlalu besar';
        }
    }

    /**
     * Submit review untuk SPD
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function submitReview(Request $request, $id)
    {
        try {
            $spd = Spd::findOrFail($id);

            // Update status ke 'Diajukan'
            $spd->update([
                'status' => 'Diajukan',
                'submitted_at' => now(),
                'submitted_by' => auth()->id()
            ]);

            return redirect()->route('bapp.approval.index')
                ->with('success', 'SPD berhasil diajukan untuk diperiksa');

        } catch (\Exception $e) {
            \Log::error('Submit SPD Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengajukan SPD: ' . $e->getMessage());
        }
    }

    /**
     * Submit BAPP ke vendor untuk approval
     */
    public function submitVendor(Request $request, $kode_bapp)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'signature' => 'required|string',
                'jenis' => 'required|in:tebang,angkut'
            ]);

            $model = $request->jenis === 'tebang' ? BappTebang::class : BappAngkut::class;
            $bapp = $model::where('kode_bapp', $kode_bapp)->firstOrFail();

            // Simpan tanda tangan ke storage
            $signature = $request->input('signature');

            // Pastikan data signature valid
            if (!preg_match('/^data:image\/\w+;base64,/', $signature)) {
                throw new \Exception('Format tanda tangan tidak valid');
            }

            $signatureImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature));

            if ($signatureImage === false) {
                throw new \Exception('Gagal mendekode tanda tangan');
            }

            // Buat direktori jika belum ada
            if (!Storage::disk('public')->exists('bapp_approvals')) {
                Storage::disk('public')->makeDirectory('bapp_approvals');
            }

            $filename = 'bapp_approvals/signature_' . time() . '_' . uniqid() . '.png';

            // Simpan file
            if (!Storage::disk('public')->put($filename, $signatureImage)) {
                throw new \Exception('Gagal menyimpan file tanda tangan');
            }

            // Update data BAPP
            $updateData = [
                'ttd_diajukan_oleh_path' => $filename,
                'status' => 'Diajukan',
                'diajukan_oleh' => auth()->user()->name,
                'tanggal_diajukan' => now()
            ];

            if (!$bapp->update($updateData)) {
                throw new \Exception('Gagal memperbarui data BAPP');
            }

            return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Error in saveSignature: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menyimpan tanda tangan digital
     */
    public function saveSignature(Request $request, $id)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'signature' => 'required|string',
                'status' => 'required|string|in:Diajukan,Diperiksa,Disetujui,Selesai',
                'signature_role' => 'required|string|in:pengaju,pemeriksa,penyetuju',
                'complaint' => 'nullable|string|max:1000',
                'jenis' => 'required|in:tebang,angkut'
            ]);

            // Tentukan model berdasarkan jenis BAPP
            $model = $validated['jenis'] === 'tebang' ? BappTebang::class : BappAngkut::class;
            
            // Cari BAPP berdasarkan ID atau kode_bapp
            $bapp = $model::where('id', $id)
                        ->orWhere('kode_bapp', $id)
                        ->first();

            if (!$bapp) {
                throw new \Exception('Data BAPP tidak ditemukan. ID/Kode: ' . $id);
            }

            // Dapatkan user yang sedang login
            $user = auth()->user();
            if (!$user) {
                throw new \Exception('User tidak terautentikasi');
            }

            // Handle signature
            $signature = $validated['signature'];
            $signaturePath = '';
            
            if ($signature) {
                $image = str_replace('data:image/png;base64,', '', $signature);
                $image = str_replace(' ', '+', $image);
                $imageName = 'signature_' . $user->id . '_' . time() . '.png';
                $path = 'signatures/' . $imageName;

                // Ensure the directory exists
                if (!file_exists(public_path('storage/signatures'))) {
                    mkdir(public_path('storage/signatures'), 0755, true);
                }
                
                file_put_contents(public_path('storage/' . $path), base64_decode($image));
                $signaturePath = $path;
            }

            // Update BAPP based on signature role
            $updates = [];
            $message = '';
            
            switch ($validated['signature_role']) {
                case 'pengaju':
                    $updates = [
                        'ttd_diajukan_oleh' => $signaturePath,
                        'diajukan_oleh' => $user->name,
                        'diajukan_pada' => now(),
                        'status' => 'Diajukan'
                    ];
                    $message = 'Tanda tangan berhasil disimpan. BAPP berhasil diajukan.';
                    break;
                    
                case 'pemeriksa':
                    $updates = [
                        'ttd_diperiksa_oleh' => $signaturePath,
                        'diperiksa_oleh' => $user->name,
                        'diperiksa_pada' => now(),
                        'status' => 'Diperiksa'
                    ];
                    
                    if ($request->filled('complaint')) {
                        $updates['keluhan_vendor'] = $validated['complaint'];
                    }
                    
                    $message = 'Tanda tangan pemeriksa berhasil disimpan.';
                    break;
                    
                case 'penyetuju':
                    $updates = [
                        'ttd_disetujui_oleh' => $signaturePath,
                        'disetujui_oleh' => $user->name,
                        'disetujui_pada' => now(),
                        'status' => 'Disetujui'
                    ];
                    $message = 'Tanda tangan penyetuju berhasil disimpan. BAPP telah disetujui.';
                    break;
            }
            
            // Update the BAPP record
            $bapp->update($updates);

            // Save complaint if exists
            if (!empty($validated['complaint']) && in_array($validated['signature_role'], ['pemeriksa', 'penyetuju'])) {
                try {
                    $complainData = [
                        'kode_bapp' => $bapp->kode_bapp,
                        'deskripsi' => $validated['complaint'],
                        'tanggal' => now()->format('Y-m-d')
                    ];

                    if ($validated['jenis'] === 'tebang') {
                        $complain = new \App\Models\ComplainBappTebang($complainData);
                    } else {
                        $complain = new \App\Models\ComplainBappAngkut($complainData);
                    }
                    
                    $complain->save();
                    
                } catch (\Exception $e) {
                    \Log::error('Gagal menyimpan keluhan: ' . $e->getMessage());
                    throw new \Exception('Tanda tangan berhasil disimpan, tetapi gagal menyimpan keluhan');
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('bapp.show', ['jenis' => $validated['jenis'], 'bapp' => $bapp->kode_bapp])
                ]);
            }

            return redirect()
                ->route('bapp.show', ['jenis' => $validated['jenis'], 'bapp' => $bapp->kode_bapp])
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Save Signature Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $errorMessage = 'Gagal menyimpan tanda tangan: ' . $e->getMessage();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Menampilkan daftar BAPP yang perlu di-approve
     */
    public function approvalIndex()
    {
        $user = auth()->user();
        $query = Spd::query();
        
        // Admin can see all data regardless of status
        if (!$user->hasRole('Admin')) {
            $statusConditions = [];
            
            // QA can only see 'Diajukan' status
            if ($user->hasRole('QA')) {
                $statusConditions[] = 'Diajukan';
            }
            
            // Manager CDR can only see 'Diperiksa' status
            if ($user->hasRole('Manager CDR')) {
                $statusConditions[] = 'Diperiksa';
            }
            
            // Director can only see 'Diverifikasi' status
            if ($user->hasRole('Director')) {
                $statusConditions[] = 'Diverifikasi';
            }
            
            // Manager Finance can only see 'Disetujui' status
            if ($user->hasRole('Manager Finance')) {
                $statusConditions[] = 'Disetujui';
            }
            
            if (!empty($statusConditions)) {
                $query->whereIn('status', $statusConditions);
            } else {
                // If user has none of the specified roles, show no data
                $query->where('id', 0);
            }
        }
        
        $spds = $query->with('diajukanOleh')
                     ->orderBy('created_at', 'desc')
                     ->get();

        return view('rekap-bapp.approval.index', compact('spds'));
    }

    /**
     * Menampilkan detail BAPP untuk approval
     */
    public function approvalShow($kode_bapp)
    {
        // Cek apakah BAPP adalah tipe Tebang atau Angkut berdasarkan prefix kode
        if (str_starts_with($kode_bapp, 'BAPPT')) {
            $bapp = BappTebang::where('kode_bapp', $kode_bapp)
                ->with([
                    'vendor',
                    'vendorAngkut',
                    'subBlock',
                    'hasilTebang' => function($query) {
                        $query->with(['subBlock']);
                    }
                ])
                ->firstOrFail();

            // Set view name based on BAPP type
            $view = 'bapp.approval.show';
            $type = 'tebang';

            // Ensure we have the related data or set defaults
            if (!$bapp->vendor) {
                $bapp->vendor = new \stdClass();
                $bapp->vendor->nama_vendor = '-';
            }
            if (!$bapp->vendorAngkut) {
                $bapp->vendorAngkut = new \stdClass();
                $bapp->vendorAngkut->nama_vendor = '-';
            }
        } else {
            // Handle BAPP Angkut
            $bapp = BappAngkut::where('kode_bapp', $kode_bapp)
                ->with([
                    'vendor',
                    'vendorAngkut',
                    'subBlock'
                ])
                ->firstOrFail();

            $view = 'bapp.approval.show';
            $type = 'angkut';

            // Ensure we have the related data or set defaults
            if (!$bapp->vendor) {
                $bapp->vendor = new \stdClass();
                $bapp->vendor->nama_vendor = '-';
            }
            if (!$bapp->vendorAngkut) {
                $bapp->vendorAngkut = new \stdClass();
                $bapp->vendorAngkut->nama_vendor = '-';
            }
        }

        return view($view, compact('bapp', 'type'));
    }

    /**
     * Proses approval vendor
     */
    public function vendorApprove(Request $request, $kode_bapp)
    {
        $request->validate([
            'signature' => 'required|string',
            'catatan' => 'nullable|string|max:500'
        ]);

        $bapp = BappTebang::where('kode_bapp', $kode_bapp)->firstOrFail();

        // Simpan tanda tangan
        $signaturePath = '';

        $signatureData = $request->signature;
        if ($signatureData) {
            // Save the signature image
            $image = str_replace('data:image/png;base64,', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'signature_' . auth()->user()->id . '_' . time() . '.png';
            $path = 'bapp_approvals/' . $imageName;

            // Ensure the directory exists
            if (!file_exists(public_path('storage/bapp_approvals'))) {
                mkdir(public_path('storage/bapp_approvals'), 0755, true);
            }

            file_put_contents(public_path('storage/' . $path), base64_decode($image));
            $signaturePath = $path;
        }

        // Update status BAPP
        $bapp->update([
            'ttd_vendor_path' => $signaturePath,
            'status' => 'pending_approval', // Berikutnya menunggu approval manager
            'catatan_vendor' => $request->catatan,
            'tanggal_approval_vendor' => now(),
        ]);

        return redirect()->route('bapp.approval.show', $bapp->kode_bapp)
            ->with('success', 'BAPP berhasil disetujui dan diajukan ke manager.');
    }

    /**
     * Proses approval final oleh manager
     */
    public function finalApprove(Request $request, $kode_bapp)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $bapp = BappTebang::where('kode_bapp', $kode_bapp)->firstOrFail();

        // Simpan tanda tangan
        $signaturePath = '';

        $signatureData = $request->signature;
        if ($signatureData) {
            // Save the signature image
            $image = str_replace('data:image/png;base64,', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'signature_' . auth()->user()->id . '_' . time() . '.png';
            $path = 'bapp_approvals/' . $imageName;

            // Ensure the directory exists
            if (!file_exists(public_path('storage/bapp_approvals'))) {
                mkdir(public_path('storage/bapp_approvals'), 0755, true);
            }

            file_put_contents(public_path('storage/' . $path), base64_decode($image));
            $signaturePath = $path;
        }

        // Update status BAPP
        $bapp->update([
            'ttd_manager_path' => $signaturePath,
            'status' => 'approved',
            'tanggal_approval_manager' => now(),
        ]);

        return redirect()->route('bapp.approval.show', $bapp->kode_bapp)
            ->with('success', 'BAPP berhasil disetujui.');
    }

    /**
     * Proses penolakan BAPP
     */
    public function rejectBAPP(Request $request, $kode_bapp)
    {
        $request->validate([
            'alasan' => 'required|string|max:500'
        ]);

        $bapp = BappTebang::where('kode_bapp', $kode_bapp)->firstOrFail();

        // Tentukan status berdasarkan role
        $status = auth()->user()->hasRole('manager-plantation') ? 'rejected' : 'draft';

        $bapp->update([
            'status' => $status,
            'alasan_penolakan' => $request->alasan,
        ]);

        return redirect()->route('bapp.approval.show', $bapp->kode_bapp)
            ->with('success', 'BAPP berhasil ditolak.');
    }

    /**
     * Show detailed view for approval
     */
    public function approvalDetail($id)
    {
        $spd = Spd::with([
            'diajukanOleh',
            'diverifikasiOleh',
            'disetujuiOleh',
            'dibayarOleh',
            'ditolakOleh',
            'diketahuiOleh'
        ])->findOrFail($id);

        // Get vendor data from BAPP Tebang for the SPD's period
        $vendorsTebang = BappTebang::with('vendor')
            ->where('periode_bapp', $spd->periode)
            ->selectRaw('vendor_tebang as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_tebang')
            ->get()
            ->map(function($item) {
                $vendor = $item->vendor ?? null;
                return [
                    'vendor' => (object)[
                        'kode_vendor' => $vendor->kode_vendor ?? $item->vendor_code,
                        'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                    ],
                    'total_pendapatan' => $item->total_pendapatan,
                    'total_tonase' => $item->total_tonase,
                    'type' => 'tebang'
                ];
            });

        // Get vendor data from BAPP Angkut for the SPD's period
        $vendorsAngkut = BappAngkut::with('vendor')
            ->where('periode_bapp', $spd->periode)
            ->selectRaw('vendor_angkut as vendor_code, SUM(total_pendapatan) as total_pendapatan, SUM(tonase_final) as total_tonase')
            ->groupBy('vendor_angkut')
            ->get()
            ->map(function($item) {
                $vendor = $item->vendor ?? null;
                return [
                    'vendor' => (object)[
                        'kode_vendor' => $vendor->kode_vendor ?? $item->vendor_code,
                        'nama_vendor' => $vendor->nama_vendor ?? 'Vendor Tidak Ditemukan',
                    ],
                    'total_pendapatan' => $item->total_pendapatan,
                    'total_tonase' => $item->total_tonase,
                    'type' => 'angkut'
                ];
            });

        // Combine both vendor data
        $vendors = $vendorsTebang->merge($vendorsAngkut);

        // Generate terbilang for the total amount
        $terbilang = $this->terbilang($spd->total_dana) . ' Rupiah';

        return view('rekap-bapp.approval.detail', compact('spd', 'terbilang', 'vendors'));
    }

    /**
     * Display the printable version of BAPP.
     *
     * @param  int  $bapp
     * @return \Illuminate\View\View
     */
    public function print($bapp)
    {
        // Get the jenis from the request
        $jenis = request('jenis', 'tebang');

        if ($jenis === 'tebang') {
            $bapp = BappTebang::with(['vendor', 'hasilTebang', 'spd.sopir', 'komplain'])->findOrFail($bapp);
            return view('bapp.print', compact('bapp', 'jenis'));
        } else {
            $bapp = BappAngkut::with(['vendor', 'hasilTebang', 'spd.sopir'])->findOrFail($bapp);
            return view('bapp.print', compact('bapp', 'jenis'));
        }
    }
}
