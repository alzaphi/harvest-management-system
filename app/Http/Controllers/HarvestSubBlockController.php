<?php

namespace App\Http\Controllers;

use App\Models\HarvestSubBlock;
use App\Models\SubBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Map;
use App\Exports\HarvestSubBlockExport;
use Maatwebsite\Excel\Facades\Excel;

class HarvestSubBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index(Request $request)
    {
        $query = $this->getHarvestSubBlocksQuery($request);

        // Check if export is requested
        if ($request->has('export') && $request->export == 'excel') {
            $harvestSubBlocks = $query->get();
            return Excel::download(new HarvestSubBlockExport($harvestSubBlocks), 'harvest_sub_blocks_' . date('Ymd_His') . '.xlsx');
        }

        // For regular view, paginate the results
        $sortField = $request->get('sort', 'planned_harvest_date');
        $sortDirection = $request->get('direction', 'asc');

        // Eager load relationships
        $withRelations = [
            'subBlock' => function($query) {
                $query->select('kode_petak', 'geom_json', 'estate', 'divisi', 'luas_area');
            },
            'trackingActivity' // Add tracking activity relationship
        ];

        // If show_single is true, get only the specific harvest sub-block
        if ($request->boolean('show_single') && $request->has('search')) {
            $harvestSubBlocks = $query->with($withRelations)
                ->where('harvest_sub_blocks.kode_petak', $request->search)
                ->orderBy($sortField, $sortDirection)
                ->paginate(1)
                ->withQueryString();
        } else {
            $harvestSubBlocks = $query->with($withRelations)
                ->orderBy($sortField, $sortDirection)
                ->orderBy('harvest_sub_blocks.kode_petak', 'asc')
                ->paginate(13)
                ->withQueryString();
        }

        return view('backend.subblock.harvest.index', compact('harvestSubBlocks'));
    }

    /**
     * Get the base query for harvest sub-blocks with filters applied
     */
    protected function getHarvestSubBlocksQuery(Request $request)
    {
        $query = HarvestSubBlock::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('harvest_sub_blocks.kode_petak', 'like', "%{$search}%")
                  ->orWhere('harvest_sub_blocks.estate', 'like', "%{$search}%")
                  ->orWhere('harvest_sub_blocks.divisi', 'like', "%{$search}%")
                  ->orWhere('harvest_sub_blocks.harvest_season', 'like', "%{$search}%")
                  ->orWhere('harvest_sub_blocks.remarks', 'like', "%{$search}%");
            });
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('harvest_sub_blocks.priority_level', $request->priority);
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->status;
            
            if ($status === 'planned') {
                // For planned status, check if there's no tracking activity
                $query->whereDoesntHave('trackingActivity');
            } else {
                // For other statuses, check the status_tracking in tracking_activities
                $query->whereHas('trackingActivity', function($q) use ($status) {
                    $q->where('status_tracking', $status);
                });
            }
        }

        return $query;
    }

    /**
     * Export harvest sub-blocks to Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $query = $this->getHarvestSubBlocksQuery($request);
        $harvestSubBlocks = $query->orderBy('planned_harvest_date', 'asc')
                                ->orderBy('kode_petak', 'asc')
                                ->get();

        return Excel::download(new HarvestSubBlockExport($harvestSubBlocks), 'harvest_sub_blocks_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get sub-blocks with age_months = 10 that are not in the harvest table yet
        $subBlocks = \App\Models\SubBlock::where('age_months', 10)
            ->whereNotIn('kode_petak', function($query) {
                $query->select('kode_petak')->from('harvest_sub_blocks');
            })
            ->orderBy('kode_petak')
            ->get();

        if ($subBlocks->isEmpty()) {
            return redirect()->route('harvest-sub-blocks.index')
                ->with('warning', 'Tidak ada sub-block dengan umur 10 bulan yang belum memiliki data panen.');
        }

        return view('backend.subblock.harvest.create', compact('subBlocks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_petak' => [
                'required',
                'string',
                'max:50',
                'exists:sub_blocks,kode_petak',
                function ($attribute, $value, $fail) {
                    $exists = HarvestSubBlock::where('kode_petak', $value)->exists();
                    if ($exists) {
                        $fail('Sub-block ini sudah memiliki data panen.');
                    }
                },
            ],
            'estate' => 'required|string|max:100',
            'divisi' => 'required|string|max:100',
            'luas_area' => 'required|numeric|min:0',
            'harvest_season' => 'required|string|max:20',
            'age_months' => 'required|integer|min:1',
            'yield_estimate_tph' => 'required|numeric|min:0',
            'planned_harvest_date' => 'required|date',
            'priority_level' => 'required|integer|min:1|max:5',
            'remarks' => 'nullable|string',
        ]);

        try {
            // Double check in case of race condition
            $exists = HarvestSubBlock::where('kode_petak', $validated['kode_petak'])->exists();
            if ($exists) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Sub-block ini sudah memiliki data panen.');
            }

            $validated['submitted_by'] = auth()->user() ? auth()->user()->name : 'System';
            HarvestSubBlock::create($validated);

            return redirect()->route('harvest-sub-blocks.index')
                           ->with('success', 'Data panen berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importFromGeojson(Request $request)
    {
        $request->validate([
            'geojson_file' => 'required|file|mimes:json,geojson',
        ]);

        $file = $request->file('geojson_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('public/geojson', $filename);

        $content = json_decode(file_get_contents(storage_path('app/' . $path)), true);

        if (!isset($content['features'])) {
            return back()->with('error', 'File tidak valid.');
        }

        $features = $content['features'];
        $plannedDate = Carbon::create(2025, 7, 12); // mulai 12 Juli
        $totalAreaPerDay = 70;
        $currentDayTotal = 0;

        foreach ($features as $f) {
            $p = $f['properties'];
            if (!isset($p['kode_petak'])) continue;

            $plannedDate = Carbon::parse($p['planned_date']);
            $umur = intval($p['age_months']);
            $zona = intval($p['zona']);
            $luas = floatval($p['luas_area']);

            // Yield estimate
            $yield = ($umur >= 13) ? rand(70, 85) : 65;

            // Planned harvest date logic
            if ($currentDayTotal + $luas > $totalAreaPerDay) {
                $plannedDate->addDay(); // ganti hari
                $currentDayTotal = 0;
            }
            $currentDayTotal += $luas;

            // Insert ke DB
            DB::table('harvest_sub_blocks')->updateOrInsert(
                ['kode_petak' => $p['kode_petak']],
                [
                    'estate' => $p['estate'],
                    'divisi' => $p['divisi'],
                    'luas_area' => $luas,
                    'harvest_season' => $plannedDate->year, // ← otomatis ambil tahun
                    'age_months' => $umur,
                    'yield_estimate_tph' => $yield,
                    'planned_harvest_date' => $plannedDate->format('Y-m-d'),
                    'priority_level' => $zona,
                    'remarks' => $p['keterangan'] ?? 'Layak Tebang',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // ✅ Tambahkan ke riwayat upload (tabel maps)
        Map::create([
            'file_name' => $filename,
            'file_path' => 'storage/geojson/' . $filename,
            'file_type' => 'geojson',
            'uploaded_by' => auth()->user()->name ?? 'Import System',
            'estate_name' => $request->input('estate_name') ?? 'Auto Import',
            'description' => 'Auto-import ke tabel harvest_sub_blocks',
            'upload_date' => now(),
        ]);

        return back()->with('success', 'Data berhasil diimpor ke tabel harvest_sub_blocks & riwayat upload!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $harvest = HarvestSubBlock::with('subBlock')->findOrFail($id);
        return view('backend.subblock.harvest.show', compact('harvest'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $harvestSubBlock = HarvestSubBlock::findOrFail($id);
        $subBlocks = \App\Models\SubBlock::orderBy('kode_petak')->get();
        return view('backend.subblock.harvest.edit', compact('harvestSubBlock', 'subBlocks'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $harvest = HarvestSubBlock::findOrFail($id);

        $validated = $request->validate([
            'kode_petak' => 'required|string|max:50|exists:sub_blocks,kode_petak',
            'estate' => 'required|string|max:100',
            'divisi' => 'required|string|max:100',
            'luas_area' => 'required|numeric|min:0',
            'harvest_season' => 'required|string|max:20',
            'age_months' => 'required|integer|min:1',
            'yield_estimate_tph' => 'required|numeric|min:0',
            'planned_harvest_date' => 'required|date',
            'priority_level' => 'required|integer|min:1|max:5',
            'remarks' => 'nullable|string',
        ]);

        try {
            $harvest->update($validated);
            return redirect()->route('harvest-sub-blocks.index')
                           ->with('success', 'Harvest data has been updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $harvest = HarvestSubBlock::findOrFail($id);
            $harvest->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data panen berhasil dihapus.'
                ]);
            }

            return redirect()->route('harvest-sub-blocks.index')
                           ->with('success', 'Data panen berhasil dihapus.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('harvest-sub-blocks.index')
                           ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Get harvest data by kode_petak
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByKodePetak(Request $request)
    {
        $kodePetak = $request->input('kode_petak');
        $harvests = HarvestSubBlock::where('kode_petak', $kodePetak)
                                 ->orderBy('tanggal_panen', 'desc')
                                 ->get();

        return response()->json($harvests);
    }
}
