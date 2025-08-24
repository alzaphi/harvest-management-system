<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SubBlockController extends Controller
{
    /**
     * Display the specified sub-block.
     *
     * @param  string  $kodePetak
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($kodePetak): JsonResponse
    {
        try {
            $subBlock = SubBlock::where('kode_petak', $kodePetak)
                ->select([
                    'estate',
                    'divisi',
                    'luas_area',
                    'zona',
                    'kode_petak',
                    'blok',
                    'aktif',
                    'keterangan'
                ])
                ->first();

            if (!$subBlock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sub-blok tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data sub-blok berhasil diambil',
                'data' => $subBlock
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching sub-block: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data sub-blok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
