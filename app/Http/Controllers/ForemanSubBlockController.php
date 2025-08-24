<?php

namespace App\Http\Controllers;

use App\Models\ForemanSubBlock;
use App\Models\SubBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForemanSubBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = ForemanSubBlock::select([
                'foreman_sub_blocks.*',
                'sub_blocks.id as id_petak',
                'sub_blocks.divisi as divisi'
            ])
            ->leftJoin('sub_blocks', 'foreman_sub_blocks.kode_petak', '=', 'sub_blocks.kode_petak')
            ->with('subBlock')
            ->orderByRaw(
                "CASE
                    WHEN sub_blocks.divisi = 'LKL1' THEN 1
                    WHEN sub_blocks.divisi = 'LKL2' THEN 2
                    WHEN sub_blocks.divisi = 'LKL3' THEN 3
                    WHEN sub_blocks.divisi = 'PLG1' THEN 4
                    WHEN sub_blocks.divisi = 'PLG2' THEN 5
                    WHEN sub_blocks.divisi = 'PLG3' THEN 6
                    ELSE 7
                END, sub_blocks.divisi, foreman_sub_blocks.kode_petak"
            );

        // Search by kode_petak
        if (request()->has('search') && !empty(request('search'))) {
            $query->where('foreman_sub_blocks.kode_petak', 'like', '%' . request('search') . '%');
        }

        // Filter by nama_mandor
        if (request()->has('nama_mandor') && !empty(request('nama_mandor'))) {
            $query->where('foreman_sub_blocks.nama_mandor', request('nama_mandor'));
        }

        $foremanSubBlocks = $query->paginate(13);
        $foremanNames = ForemanSubBlock::select('nama_mandor')->distinct()->orderBy('nama_mandor')->pluck('nama_mandor');

        return view('backend.subblock.foreman-subblock.foreman', compact('foremanSubBlocks', 'foremanNames'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get unique kode_petak from harvest_sub_blocks that are not already in foreman_sub_blocks
        $subBlocks = \App\Models\HarvestSubBlock::select('kode_petak', 'divisi')
            ->whereNotIn('kode_petak', function($query) {
                $query->select('kode_petak')
                    ->from('foreman_sub_blocks');
            })
            ->groupBy('kode_petak', 'divisi')
            ->get();

        // Get active foremen from the foreman table (status = 'Aktif')
        $foremanNames = \App\Models\Foreman::select('nama_mandor', 'kode_mandor')
            ->where('status', 'Aktif')
            ->orderBy('nama_mandor')
            ->get();

        return view('backend.subblock.foreman-subblock.create', compact('subBlocks', 'foremanNames'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_petak' => 'required|string|exists:sub_blocks,kode_petak',
                'divisi' => 'required|string|max:100',
                'kode_mandor' => 'required|string|max:10',
                'nama_mandor' => 'required|string|max:100',
            ]);

            // Check if kode_petak already exists
            $exists = ForemanSubBlock::where('kode_petak', $validated['kode_petak'])->exists();
            if ($exists) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kode petak sudah terdaftar',
                    ], 422);
                }
                return back()->with('error', 'Kode petak sudah terdaftar')->withInput();
            }

            ForemanSubBlock::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => route('foreman-sub-blocks.index')
                ]);
            }

            return redirect()->route('foreman-sub-blocks.index')
                ->with('success', 'Data mandor sub block berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error creating foreman sub block: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ForemanSubBlock  $foremanSubBlock
     * @return \Illuminate\Http\Response
     */
    public function show(ForemanSubBlock $foremanSubBlock)
    {
        return view('backend.subblock.foreman-subblock.show', compact('foremanSubBlock'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ForemanSubBlock  $foremanSubBlock
     * @return \Illuminate\Http\Response
     */
    public function edit(ForemanSubBlock $foremanSubBlock)
    {
        $subBlocks = SubBlock::select('kode_petak', 'divisi')->get();

        // Get active foremen from the foreman table (status = 'Aktif')
        $foremen = \App\Models\Foreman::select('nama_mandor', 'kode_mandor')
            ->where('status', 'Aktif')
            ->orderBy('nama_mandor')
            ->get();

        return view('backend.subblock.foreman-subblock.edit', compact('foremanSubBlock', 'subBlocks', 'foremen'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForemanSubBlock  $foremanSubBlock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForemanSubBlock $foremanSubBlock)
    {
        try {
            $validated = $request->validate([
                'kode_petak' => 'required|string|exists:sub_blocks,kode_petak',
                'divisi' => 'required|string|max:100',
                'kode_mandor' => 'required|string|max:10',
                'nama_mandor' => 'required|string|max:100',
            ]);

            $foremanSubBlock->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data mandor sub block berhasil diperbarui',
                    'redirect' => route('foreman-sub-blocks.index')
                ]);
            }

            return redirect()->route('foreman-sub-blocks.index')
                ->with('success', 'Data mandor sub block berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error updating foreman sub block: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ForemanSubBlock  $foremanSubBlock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ForemanSubBlock $foremanSubBlock)
    {
        try {
            $foremanSubBlock->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data mandor sub block berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting foreman sub block: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data mandor sub block'
            ], 500);
        }
    }
}
