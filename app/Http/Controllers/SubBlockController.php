<?php

namespace App\Http\Controllers;

use App\Models\SubBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Response;

class SubBlockController extends Controller
{
    // Middleware auth sementara dinonaktifkan untuk testing
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        try {
            // Debug: Log request data
            Log::info('SubBlock index request', ['request' => $request->all()]);

            $query = SubBlock::query();

            // Search functionality
            if ($request->filled('kode_petak')) {
                $query->where('kode_petak', 'like', '%' . $request->kode_petak . '%');
            }

            // Get distinct estates
            $estates = SubBlock::select('estate')
                ->distinct()
                ->orderBy('estate')
                ->pluck('estate');

            // Get divisions grouped by estate
            $estatesWithDivisions = [];
            foreach ($estates as $estate) {
                $estatesWithDivisions[$estate] = SubBlock::where('estate', $estate)
                    ->select('divisi')
                    ->distinct()
                    ->orderBy('divisi')
                    ->pluck('divisi')
                    ->toArray();
            }

            $subblocks = $query->paginate(13)->onEachSide(1);

            // Set breadcrumb
            $breadcrumb = [
                ['title' => 'Dashboard', 'url' => route('dashboard')],
                ['title' => 'List Sub Block']
            ];

            return view('backend.subblock.index', [
                'subblocks' => $subblocks,
                'estates' => $estates,
                'estatesWithDivisions' => $estatesWithDivisions,
                'breadcrumb' => $breadcrumb
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@index: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data sub block: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            // Get all unique estates with their divisions
            $estatesData = SubBlock::select('estate')
                ->distinct()
                ->orderBy('estate')
                ->get()
                ->mapWithKeys(function($item) {
                    $displayName = str_replace('Estate ', '', $item->estate);
                    return [$displayName => $item->estate];
                });

            $estates = $estatesData->keys()->unique()->values();
            $estatesWithDivisions = [];
            $blocks = [];

            // Build the divisions array with display names as keys
            foreach ($estatesData as $displayName => $estate) {
                $estatesWithDivisions[$displayName] = SubBlock::where('estate', $estate)
                    ->select('divisi')
                    ->distinct()
                    ->orderBy('divisi')
                    ->pluck('divisi')
                    ->toArray();

                // Get blocks for each estate and division
                $estateBlocks = SubBlock::where('estate', $estate)
                    ->select('divisi', 'blok')
                    ->distinct()
                    ->get()
                    ->toArray();
                
                $blocks = array_merge($blocks, $estateBlocks);
            }

            // Set breadcrumb
            $breadcrumb = [
                ['title' => 'List Sub Block', 'url' => route('sub-blocks.index')],
                ['title' => 'Tambah Sub Block']
            ];

            return view('backend.subblock.create', [
                'estates' => $estates,
                'estatesWithDivisions' => $estatesWithDivisions,
                'blocks' => $blocks,
                'breadcrumb' => $breadcrumb
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@create: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat form tambah sub block: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kode_petak' => 'required|string|max:50|unique:sub_blocks,kode_petak',
                'estate' => 'required|string|max:100',
                'divisi' => 'required|string|max:100',
                'blok' => 'required|string|max:50',
                'luas_area' => 'required|numeric|min:0',
                'age_months' => 'nullable|integer|min:0',
                'zona' => 'nullable|string|max:50',
                'keterangan' => 'nullable|string|max:255',
                'aktif' => 'boolean',
                'geom_json' => 'nullable|json',
            ]);

            // Additional validation for geom_json format
            $validator->after(function ($validator) use ($request) {
                if ($request->filled('geom_json')) {
                    $geomData = json_decode($request->geom_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validator->errors()->add('geom_json', 'Format GeoJSON tidak valid');
                        return;
                    }

                    if (!isset($geomData['type']) || $geomData['type'] !== 'Polygon') {
                        $validator->errors()->add('geom_json', 'Tipe GeoJSON harus berupa "Polygon"');
                    }

                    if (!isset($geomData['coordinates']) || !is_array($geomData['coordinates'])) {
                        $validator->errors()->add('geom_json', 'Koordinat tidak valid');
                        return;
                    }

                    $coords = $geomData['coordinates'][0] ?? [];
                    if (count($coords) < 4) {
                        $validator->errors()->add('geom_json', 'Polygon membutuhkan minimal 4 titik koordinat');
                    } else {
                        $first = $coords[0];
                        $last = $coords[count($coords) - 1];
                        if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
                            $validator->errors()->add('geom_json', 'Polygon harus tertutup (titik awal dan akhir harus sama)');
                        }
                    }
                }
            });

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()->toArray()
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->only([
                'kode_petak', 'divisi', 'blok',
                'luas_area', 'age_months', 'zona', 'keterangan', 'aktif', 'geom_json'
            ]);

            // Store the estate value directly without adding 'Estate ' prefix
            $data['estate'] = $request->input('estate');
            $data['aktif'] = $request->boolean('aktif');

            SubBlock::create($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sub block berhasil disimpan',
                    'redirect' => route('sub-blocks.index')
                ]);
            }

            return redirect()->route('sub-blocks.index')
                ->with('success', 'Data sub block berhasil disimpan');

        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@store: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $subblock = SubBlock::findOrFail($id);

            // Get all unique estates with their divisions
            $estates = SubBlock::select('estate')
                ->distinct()
                ->orderBy('estate')
                ->pluck('estate');

            // Get divisions for the current estate
            $divisions = SubBlock::where('estate', $subblock->estate)
                ->select('divisi')
                ->distinct()
                ->pluck('divisi');

            // Get blocks for the current division
            $blocks = SubBlock::where('estate', $subblock->estate)
                ->where('divisi', $subblock->divisi)
                ->select('blok')
                ->distinct()
                ->pluck('blok');

            // Get all estates with their divisions for the dropdown
            $estatesWithDivisions = [];
            foreach ($estates as $estate) {
                $estatesWithDivisions[$estate] = SubBlock::where('estate', $estate)
                    ->select('divisi')
                    ->distinct()
                    ->orderBy('divisi')
                    ->pluck('divisi')
                    ->toArray();
            }

            // Set breadcrumb
            $breadcrumb = [
                ['title' => 'List Sub Block', 'url' => route('sub-blocks.index')],
                ['title' => 'Edit Sub Block']
            ];

            return view('backend.subblock.edit', [
                'subblock' => $subblock,
                'estates' => $estates,
                'divisions' => $divisions,
                'blocks' => $blocks,
                'estatesWithDivisions' => $estatesWithDivisions,
                'breadcrumb' => $breadcrumb
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@edit: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat form edit sub block: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $subBlock = SubBlock::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kode_petak' => 'required|string|max:50|unique:sub_blocks,kode_petak,' . $id,
                'estate' => 'required|string|max:100',
                'divisi' => 'required|string|max:100',
                'blok' => 'required|string|max:50',
                'luas_area' => 'required|numeric|min:0',
                'age_months' => 'nullable|integer|min:0',
                'zona' => 'nullable|string|max:50',
                'keterangan' => 'nullable|string|max:255',
                'aktif' => 'boolean',
                'geom_json' => 'nullable|json',
            ]);

            // Additional validation for geom_json format
            $validator->after(function ($validator) use ($request) {
                if ($request->filled('geom_json')) {
                    $geomData = json_decode($request->geom_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validator->errors()->add('geom_json', 'Format GeoJSON tidak valid');
                        return;
                    }

                    if (!isset($geomData['type']) || $geomData['type'] !== 'Polygon') {
                        $validator->errors()->add('geom_json', 'Tipe GeoJSON harus berupa "Polygon"');
                    }

                    if (!isset($geomData['coordinates']) || !is_array($geomData['coordinates'])) {
                        $validator->errors()->add('geom_json', 'Koordinat tidak valid');
                        return;
                    }

                    $coords = $geomData['coordinates'][0] ?? [];
                    if (count($coords) < 4) {
                        $validator->errors()->add('geom_json', 'Polygon membutuhkan minimal 4 titik koordinat');
                    } else {
                        $first = $coords[0];
                        $last = $coords[count($coords) - 1];
                        if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
                            $validator->errors()->add('geom_json', 'Polygon harus tertutup (titik awal dan akhir harus sama)');
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $request->only([
                'kode_petak', 'divisi', 'blok',
                'luas_area', 'zona', 'keterangan', 'aktif', 'geom_json'
            ]);

            // Store the estate value directly without adding 'Estate ' prefix
            $data['estate'] = $request->input('estate');
            $data['aktif'] = $request->boolean('aktif');

            $subBlock->update($data);

            return redirect()->route('sub-blocks.index')
                ->with('success', 'Data sub block berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@update: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui data sub block: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $subBlock = SubBlock::findOrFail($id);
            $subBlock->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data sub block berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SubBlockController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data sub block: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTebangGeojson()
    {
        $features = \DB::table('sub_blocks')
            ->where('age_months', '>=', 11)
            ->get();

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($features as $block) {
            $geometry = json_decode($block->geom_json, true);

            $geojson['features'][] = [
            'type' => 'Feature',
            'geometry' => $geometry,
            'properties' => [
                'kode_petak' => $block->kode_petak,
                'estate' => $block->estate,
                'divisi' => $block->divisi,
                'blok' => $block->blok,
                'luas_area' => $block->luas_area,
                'age_months' => $block->age_months,
                'zona' => $block->zona,
                'keterangan' => $block->keterangan,
            ]
        ];
    }

    $filename = 'layak_tebang.geojson';
    $content = json_encode($geojson, JSON_PRETTY_PRINT);

    return response()->make($content, 200, [
            'Content-Type' => 'application/geo+json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    public function getBlocksByDivision(Request $request)
    {
        try {
            $divisi = $request->input('divisi');

            if (empty($divisi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter divisi diperlukan'
                ], 400);
            }

            $blocks = DB::table('sub_blocks')
                ->where('divisi', $divisi)
                ->select('blok')
                ->distinct()
                ->orderBy('blok')
                ->get()
                ->map(function($item) {
                    return [
                        'blok' => $item->blok,
                        'value' => $item->blok
                    ];
                });

            return response()->json($blocks);

        } catch (\Exception $e) {
            Log::error('Error in getBlocksByDivision: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data blok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export all subblocks as GeoJSON
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Import sub-blocks from GeoJSON file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importGeoJson(Request $request)
    {
        $request->validate([
            'geojson_file' => 'required|file|mimes:json,geojson|max:10240', // 10MB max
            'update_existing' => 'sometimes|boolean'
        ]);

        try {
            $file = $request->file('geojson_file');
            $updateExisting = $request->boolean('update_existing');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('File is not a valid JSON');
            }

            if (!isset($data['type']) || $data['type'] !== 'FeatureCollection' || !isset($data['features'])) {
                throw new \Exception('File is not a valid GeoJSON FeatureCollection');
            }

            $imported = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($data['features'] as $index => $feature) {
                try {
                    if (!isset($feature['properties']['kode_petak'])) {
                        $errors[] = "Feature at index {$index} is missing required 'kode_petak' property";
                        continue;
                    }

                    $subBlockData = [
                        'kode_petak' => $feature['properties']['kode_petak'],
                        'estate' => $feature['properties']['estate'] ?? null,
                        'divisi' => $feature['properties']['divisi'] ?? null,
                        'blok' => $feature['properties']['blok'] ?? null,
                        'luas_area' => $feature['properties']['luas_area'] ?? 0,
                        'zona' => $feature['properties']['zona'] ?? null,
                        'age_months' => $feature['properties']['age_months'] ?? null,
                        'keterangan' => $feature['properties']['keterangan'] ?? null,
                        'aktif' => $feature['properties']['aktif'] ?? true,
                        'geom_json' => $feature['geometry'] ?? null
                    ];

                    if ($updateExisting) {
                        $subBlock = SubBlock::updateOrCreate(
                            ['kode_petak' => $subBlockData['kode_petak']],
                            $subBlockData
                        );
                        $updated++;
                    } else {
                        SubBlock::create($subBlockData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing feature at index {$index}: " . $e->getMessage();
                    continue;
                }
            }

            DB::commit();

            $message = "Successfully imported {$imported} sub-blocks" . 
                      ($updateExisting ? " and updated {$updated} existing sub-blocks" : "") . ".";
            
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred during import.";
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors
                ], 207); // 207 Multi-Status
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'updated' => $updateExisting ? $updated : 0
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to import GeoJSON: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export all subblocks as GeoJSON
     *
     * @return \Illuminate\Http\Response
     */
    public function exportGeoJson()
    {
        try {
            $subblocks = SubBlock::whereNotNull('geom_json')
                ->where('aktif', true)
                ->get();
                
            $features = [];
            
            foreach ($subblocks as $subblock) {
                $geometry = $subblock->geom_json;
                
                if (is_string($geometry)) {
                    $geometry = json_decode($geometry, true);
                }
                
                if (is_array($geometry) && !empty($geometry)) {
                    $feature = [
                        'type' => 'Feature',
                        'geometry' => $geometry,
                        'properties' => [
                            'kode_petak' => $subblock->kode_petak,
                            'estate' => $subblock->estate,
                            'divisi' => $subblock->divisi,
                            'blok' => $subblock->blok,
                            'luas_area' => $subblock->luas_area,
                            'zona' => $subblock->zona,
                            'age_months' => $subblock->age_months,
                            'keterangan' => $subblock->keterangan,
                            'aktif' => $subblock->aktif,
                            'geom_json' => $geometry
                        ]
                    ];
                    
                    $features[] = $feature;
                }
            }
            
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => $features
            ];
            
            $filename = 'subblocks_export_' . date('Y-m-d') . '.geojson';
            
            return Response::make(json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename=' . $filename);
                
        } catch (\Exception $e) {
            Log::error('Error exporting GeoJSON: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data GeoJSON: ' . $e->getMessage());
        }
    }
}
