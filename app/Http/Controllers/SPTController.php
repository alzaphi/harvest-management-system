<?php

namespace App\Http\Controllers;

use App\Models\SPT;
use App\Models\ForemanSubBlock;
use App\Models\Vendor;
use App\Models\HarvestSubBlock;
use App\Models\TrackingActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\SubBlock;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class SPTController extends Controller
{


    /**
     * Save signature to storage
     */
    private function saveSignature($signatureData)
    {
        try {
            $directory = 'signatures';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $image = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $imageName = $directory . '/' . Str::random(40) . '.png';

            Storage::disk('public')->put($imageName, base64_decode($image));

            return $imageName;
        } catch (\Exception $e) {
            Log::error('Error saving signature: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * Get vendor details by kode_vendor
     */
    public function getVendorDetails($vendor)
    {
        $vendor = Vendor::where('kode_vendor', $vendor)->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_tenaga_kerja' => $vendor->jumlah_tenaga_kerja ?? 0
            ]
        ]);
    }

    /**
     * Get sub-block information including mandor data
     */
    public function getSubBlockInfo($kodePetak)
    {
        try {
            // Log the incoming request
            \Log::info('getSubBlockInfo called with kode_petak: ' . $kodePetak);

            $subBlock = \App\Models\SubBlock::where('kode_petak', $kodePetak)->first();

            if (!$subBlock) {
                \Log::warning('Sub-block not found for kode_petak: ' . $kodePetak);
                return response()->json([
                    'success' => false,
                    'message' => 'Sub-block not found'
                ], 404);
            }

            // Get mandor information from foreman_sub_blocks
            $foremanSubBlock = \App\Models\ForemanSubBlock::where('kode_petak', $kodePetak)->first();
            \Log::info('Foreman sub-block query result:', ['exists' => $foremanSubBlock ? 'yes' : 'no', 'data' => $foremanSubBlock]);

            $data = [
                'estate' => $subBlock->estate,
                'divisi' => $subBlock->divisi,
                'luas_area' => $subBlock->luas_area,
                'zona' => $subBlock->zona,
                'kode_mandor' => $foremanSubBlock ? $foremanSubBlock->kode_mandor : null,
                'nama_mandor' => $foremanSubBlock ? $foremanSubBlock->nama_mandor : null
            ];

            \Log::info('Returning sub-block data:', $data);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sub-block information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // Get current user role and username
        $user = auth()->user();
        $userRole = $user->role_name;
        $username = $user->username;

        // Log user info for debugging
        \Log::info('User accessing SPT index', [
            'user_id' => $user->id,
            'username' => $username,
            'role' => $userRole
        ]);

        $query = SPT::with([
                'vendor', 
                'foremanSubBlock',
                'confirmations' => function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('role_name', 'mandor');
                }
            ])
            ->latest();

        // Apply role-based filtering
        switch ($userRole) {
            case 'mandor':
                // Get mandor's kode_mandor from foreman table
                $mandor = \App\Models\Foreman::where('email', $username)
                    ->orWhere('kode_mandor', $username)
                    ->first();
                
                if ($mandor) {
                    // For Mandor, only show SPTs with status 'Disetujui' and assigned to them
                    $kodeMandor = $mandor->kode_mandor;
                    $query->where('status', 'Disetujui')
                          ->where('kode_mandor', $kodeMandor);
                    
                    // Log the raw SQL query for debugging
                    \Log::info('SPT Query for Mandor:', [
                        'user_email' => $username,
                        'kode_mandor' => $kodeMandor,
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                } else {
                    // If no mandor found, return empty result
                    $query->whereRaw('1=0');
                    \Log::warning('No mandor found for user: ' . $username);
                }
                break;
                
            case 'Assistant Manager Plantation':
                // Show SPTs with status 'Diajukan', 'Diperiksa', or 'Disetujui'
                $query->whereIn('status', ['Diajukan', 'Diperiksa', 'Disetujui']);
                // Modify status display for Assistant Manager view
                $query->selectRaw("*, 
                    CASE 
                        WHEN ttd_diperiksa_oleh_path IS NULL AND status = 'Diajukan' THEN 'Waiting' 
                        WHEN ttd_diperiksa_oleh_path IS NOT NULL AND status = 'Diajukan' THEN 'Diperiksa'
                        WHEN status = 'Diperiksa' THEN 'Diperiksa'
                        WHEN status = 'Disetujui' THEN 'Disetujui'
                        ELSE status 
                    END as display_status");
                break;
                
            case 'Manager Plantation':
                // Show SPTs with status 'Diperiksa' or 'Disetujui'
                $query->whereIn('status', ['Diperiksa', 'Disetujui']);
                // Modify status display for Manager view
                $query->selectRaw("*, 
                    CASE 
                        WHEN ttd_disetujui_oleh_path IS NULL AND status = 'Diperiksa' THEN 'Waiting' 
                        WHEN ttd_disetujui_oleh_path IS NOT NULL AND status = 'Diperiksa' THEN 'Disetujui'
                        WHEN status = 'Disetujui' THEN 'Disetujui'
                        ELSE status 
                    END as display_status");
                break;
                
            default: // Admin and Assistant Divisi Plantation
                // Show all SPTs without status filtering
                $query->selectRaw("*, status as display_status");
                break;

            case 'vendor':
                // For Vendor users, only show SPTs with status 'Disetujui' (Approved)
                \Log::info('Vendor user accessing SPT index', [
                    'user_id' => $user->id,
                    'username' => $username,
                    'name' => $user->name
                ]);
                
                // Filter SPTs by vendor name and status 'Disetujui'
                $query->where('status', 'Disetujui')
                    ->whereHas('vendor', function($q) use ($user) {
                        $q->where('nama_vendor', $user->name);
                    });
                
                // Log the final query for debugging
                \Log::info('Final SPT query for vendor:', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);
                break;
                
            case 'Assistant Manager CDR':
                $query->whereIn('status', ['Disetujui','Selesai']);
                break;

            case 'Manager CDR':
                $query->whereIn('status', ['Disetujui','Selesai']);
                break;

            case 'Director':
                $query->whereIn('status', ['Disetujui', 'Diajukan', 'Diperiksa', 'Selesai']);
                break;
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_spt', 'like', "%{$search}%")
                  ->orWhere('jenis_tebang', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('nama_vendor', 'like', "%{$search}%");
                  });
            });
        }

        // Apply filters
        if ($request->has('tanggal_mulai') && !empty($request->tanggal_mulai)) {
            $query->whereDate('tanggal_tebang', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && !empty($request->tanggal_selesai)) {
            $query->whereDate('tanggal_tebang', '<=', $request->tanggal_selesai);
        }

        if ($request->has('jenis_tebang') && !empty($request->jenis_tebang)) {
            $query->where('jenis_tebang', $request->jenis_tebang);
        }

        // Apply status filter for Admin, Assistant Divisi, and Assistant Manager Plantation
        if (in_array($userRole, ['admin', 'Assistant Divisi Plantation', 'Assistant Manager Plantation', 'Manager Plantation']) && $request->has('status') && !empty($request->status)) {
            if ($request->status === 'Waiting' && $userRole === 'Assistant Manager Plantation') {
                // For Assistant Manager, 'Waiting' means status is 'Diajukan'_oleh_path is NULL
                $query->where('status', 'Diajukan')
                      ->whereNull('ttd_diperiksa_oleh_path');
            } else if ($request->status === 'Waiting' && $userRole === 'Manager Plantation') {
                // For Manager, 'Waiting' means status is 'Diperiksa' and ttd_disetujui_oleh_path is NULL
                $query->where('status', 'Diperiksa')
                      ->whereNull('ttd_disetujui_oleh_path');
            } else {
                // For other statuses or roles, use the status as is
                $query->where('status', $request->status);
            }
        }

        $spts = $query->paginate(10);

        return view('spt.index', compact('spts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get the next SPT number in format SPT-XXX
        $lastSPT = SPT::where('kode_spt', 'like', 'SPT-%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastSPT) {
            $lastNumber = (int)substr($lastSPT->kode_spt, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format the number with leading zeros (001, 002, etc.)
        $nextSPTNumber = 'SPT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Get active vendors (tebang only)
        $vendors = Vendor::where('status', 'Aktif')
            ->where('kode_vendor', 'like', 'VT%')
            ->get();

        // Get the earliest planned_harvest_date from harvest_sub_blocks
        $minHarvestDate = HarvestSubBlock::min('planned_harvest_date');

        // Get all harvest sub-blocks with their subBlock relationship
        $harvestSubBlocks = HarvestSubBlock::with('subBlock')->get();

        // Get kode_petak that have COMPLETED SPTs from tracking activity
        $completedPetaks = TrackingActivity::where('status_tracking', 'completed')
            ->pluck('kode_petak')
            ->unique()
            ->values()
            ->toArray();

        // Get foreman sub-blocks for mandor assignment, excluding completed petaks
        $foremanSubBlocks = ForemanSubBlock::select('kode_petak')
            ->whereNotIn('kode_petak', $completedPetaks)
            ->distinct()
            ->get();

        // Get additional information from sub_blocks table for non-completed petaks
        $subBlocks = SubBlock::whereIn('kode_petak', $foremanSubBlocks->pluck('kode_petak'))
            ->get()
            ->keyBy('kode_petak');

        // Only load mandors if there's a petak selected (form validation failed)
        $mandors = collect();
        if (old('kode_petak')) {
            $mandors = ForemanSubBlock::where('kode_petak', old('kode_petak'))
                ->with('foreman')
                ->get()
                ->pluck('foreman')
                ->filter()
                ->unique('kode_mandor');
        }

        return view('spt.create', [
            'vendors' => $vendors,
            'harvestSubBlocks' => $harvestSubBlocks,
            'foremanSubBlocks' => $foremanSubBlocks,
            'subBlocks' => $subBlocks,
            'mandors' => $mandors,
            'nextSPTNumber' => $nextSPTNumber,
            'minHarvestDate' => $minHarvestDate,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug log the incoming request
        \Log::info('=== STORE SPT REQUEST ===');
        \Log::info('Request Data:', $request->all());

        // Check SPT creation restrictions
        $kodePetak = $request->kode_petak;
        $vendorId = $request->kode_vendor_tebang;
        
        // 1. Check if kode_petak has any COMPLETED SPTs based on tracking
        if (SPT::hasCompletedSpt($kodePetak)) {
            return back()
                ->withInput()
                ->with('error', 'Tidak dapat membuat SPT untuk kode petak ini karena sudah memiliki SPT yang SELESAI (berdasarkan tracking).');
        }
        
        // 2. Check if vendor already has an active SPT for this kode_petak
        if (SPT::vendorHasSptForPetak($vendorId, $kodePetak)) {
            return back()
                ->withInput()
                ->with('error', 'Vendor ini sudah memiliki SPT aktif untuk kode petak yang dipilih.');
        }
        
        // 3. Check if kode_petak already has 3 SPTs with different vendors
        $vendorCount = SPT::getDifferentVendorSptCount($kodePetak);
        if ($vendorCount >= 3) {
            return back()
                ->withInput()
                ->with('error', 'Kode petak ini sudah mencapai batas maksimal 3 SPT dengan vendor yang berbeda.');
        }
        
        // 4. Check if an active SPT (not completed/cancelled) was created for this kode_petak within the last 3 days
        if (SPT::hasRecentSpt($kodePetak)) {
            return back()
                ->withInput()
                ->with('error', 'Tidak dapat membuat SPT baru untuk kode petak ini karena sudah ada SPT aktif yang dibuat dalam 3 hari terakhir.');
        }

        // Validate request
        $validated = $request->validate([
            'kode_spt' => 'required|string|max:50|unique:spt,kode_spt',
            'kode_vendor_tebang' => 'required|string|exists:vendor_angkut,kode_vendor',
            'kode_petak' => 'required|exists:harvest_sub_blocks,kode_petak',
            'kode_mandor' => 'required|exists:foreman_sub_blocks,kode_mandor',
            'tanggal_tebang' => 'required|date',
            'jumlah_tenaga_kerja' => 'nullable|integer|min:1',
            'jumlah_tenaga_kerja_value' => 'required|integer|min:1',
            'jenis_tebang' => 'required|string|in:Manual,Semi-Mekanis,Mekanis',
            'catatan' => 'nullable|string',
            'dibuat_oleh' => 'required|string|max:100',
            'diperiksa_oleh' => 'required|string|max:100',
            'disetujui_oleh' => 'required|string|max:100',
        ], [
            'kode_spt.required' => 'Nomor SPT wajib diisi',
            'kode_spt.unique' => 'Nomor SPT sudah digunakan',
            'kode_vendor_tebang.required' => 'Vendor tebang wajib dipilih',
            'kode_petak.required' => 'Kode petak wajib dipilih',
            'kode_mandor.required' => 'Mandor wajib dipilih',
            'tanggal_tebang.required' => 'Tanggal tebang wajib diisi',
            'jumlah_tenaga_kerja.required' => 'Jumlah tenaga kerja wajib diisi',
            'jenis_tebang.required' => 'Jenis tebang wajib dipilih',
            'dibuat_oleh.required' => 'Nama pembuat wajib diisi',
            'diperiksa_oleh.required' => 'Nama pemeriksa wajib diisi',
            'disetujui_oleh.required' => 'Nama yang menyetujui wajib diisi',
        ]);

        // Verify mandor exists for the selected petak
        $mandor = \App\Models\ForemanSubBlock::where('kode_mandor', $validated['kode_mandor'])
            ->where('kode_petak', $validated['kode_petak'])
            ->first();

        if (!$mandor) {
            \Log::error('Mandor not found for petak', [
                'kode_mandor' => $validated['kode_mandor'],
                'kode_petak' => $validated['kode_petak']
            ]);
            return back()
                ->withInput()
                ->with('error', 'Mandor yang dipilih tidak tersedia untuk petak ini.');
        }

        // Start database transaction
        \DB::beginTransaction();

        try {
            // Create SPT record
            $spt = new SPT();
            $spt->kode_spt = $validated['kode_spt'];
            $spt->kode_vendor = $validated['kode_vendor_tebang'];
            $spt->kode_mandor = $validated['kode_mandor'];
            $spt->kode_petak = $validated['kode_petak'];
            $spt->tanggal_tebang = $validated['tanggal_tebang'];
            $spt->jumlah_tenaga_kerja = $validated['jumlah_tenaga_kerja_value'];
            $spt->jenis_tebang = $validated['jenis_tebang'];
            $spt->catatan = $validated['catatan'] ?? null;
            $spt->dibuat_oleh = $validated['dibuat_oleh'];
            $spt->diperiksa_oleh = $validated['diperiksa_oleh'];
            $spt->disetujui_oleh = $validated['disetujui_oleh'];
            $spt->status = 'Draft';
            $spt->approval_stage = SPT::STAGE_DRAFT;

            if (!$spt->save()) {
                throw new \Exception('Gagal menyimpan data SPT ke database');
            }

            // Create tracking activity for the new SPT
            $trackingActivity = new TrackingActivity();
            $trackingActivity->kode_spt = $spt->kode_spt;
            $trackingActivity->kode_petak = $spt->kode_petak;
            $trackingActivity->status_tracking = 'not_started';
            $trackingActivity->updated_by = auth()->user()->name ?? 'System';
            
            if (!$trackingActivity->save()) {
                throw new \Exception('Gagal membuat tracking activity untuk SPT');
            }

            \DB::commit();
            \Log::info('SPT and tracking activity created successfully', [
                'spt_id' => $spt->id,
                'tracking_activity_id' => $trackingActivity->id
            ]);

            // Clear all flash messages to prevent duplicates
            session()->forget(['success', 'message']);

            // Set success message that will be shown as toast
            $message = 'Surat Perintah Tebang berhasil disimpan!';
            
            // Set flash data for toast notification
            session()->flash('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
                'position' => 'top-end',
                'timer' => 5000,
                'showConfirmButton' => false,
                'timerProgressBar' => true
            ]);
            
            // Redirect to show page
            return redirect()->route('spt.show', $spt->id);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error during SPT creation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            $errorMessage = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();

            if (str_contains($e->getMessage(), 'SQLSTATE[23000]')) {
                if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                    $errorMessage = 'Data yang dipilih tidak valid. Pastikan vendor, petak, dan mandor yang dipilih tersedia dan valid.';
                } elseif (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $errorMessage = 'Nomor SPT sudah digunakan. Silakan refresh halaman untuk mendapatkan nomor baru.';
                }
            }

            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SPT $spt)
    {
        // Eager load relationships
        $spt->load(['vendor', 'harvestSubBlock', 'foremanSubBlock', 'subBlock']);

        return view('spt.show', compact('spt'));
    }

    /**
     * Generate PDF for the specified SPT.
     */
    public function downloadPdf(SPT $spt)
    {
        // Eager load relationships
        $spt->load(['vendor', 'harvestSubBlock', 'foremanSubBlock', 'subBlock']);

        // Generate PDF
        $pdf = PDF::loadView('spt.pdf', compact('spt'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Download the PDF with a custom filename
        return $pdf->download('SPT-' . $spt->kode_spt . '.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SPT $spt)
    {
        $vendors = Vendor::where('status', 'Aktif')
            ->where('kode_vendor', 'like', 'VT%')
            ->get();

        // Get kode_petak from foreman_sub_blocks table
        $foremanSubBlocks = ForemanSubBlock::select('kode_petak')
            ->distinct()
            ->get();

        // Get additional information from sub_blocks table
        $subBlocks = SubBlock::whereIn('kode_petak', $foremanSubBlocks->pluck('kode_petak'))
            ->get()
            ->keyBy('kode_petak');

        // Get vendor count for each kode_petak
        $vendorCounts = SPT::select('kode_petak', DB::raw('count(distinct kode_vendor) as vendor_count'))
            ->whereIn('kode_petak', $foremanSubBlocks->pluck('kode_petak'))
            ->groupBy('kode_petak')
            ->get()
            ->keyBy('kode_petak');

        // Combine the data
        $harvestSubBlocks = $foremanSubBlocks->map(function($item) use ($subBlocks, $vendorCounts) {
            $subBlock = $subBlocks->get($item->kode_petak);
            $vendorCount = $vendorCounts->get($item->kode_petak);
            
            return (object)[
                'kode_petak' => $item->kode_petak,
                'blok' => $subBlock ? $subBlock->blok : 'Blok Tidak Diketahui',
                'luas_area' => $subBlock ? $subBlock->luas_area : 0,
                'vendor_count' => $vendorCount ? $vendorCount->vendor_count : 0
            ];
        });

        // Get unique mandors
        $mandors = ForemanSubBlock::select('kode_mandor', 'nama_mandor')
            ->distinct()
            ->orderBy('kode_mandor')
            ->get();

        return view('spt.edit', compact('spt', 'vendors', 'harvestSubBlocks', 'mandors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SPT $spt)
    {
        $validated = $request->validate([
            'kode_spt' => 'required|string|max:20|unique:spt,kode_spt,' . $spt->id,
            'kode_vendor' => 'required|string|max:20',
            'kode_mandor' => 'required|string|max:10',
            'kode_petak' => 'required|string|max:50',
            'tanggal_tebang' => 'required|date',
            'jumlah_tenaga_kerja' => 'required|integer|min:1',
            'jenis_tebang' => 'required|string|max:50',
            'catatan' => 'nullable|string',
            'dibuat_oleh' => 'required|string|max:100',
            'diperiksa_oleh' => 'required|string|max:100',
            'disetujui_oleh' => 'required|string|max:100',
        ]);

        try {
            // Get mandor information from foreman_sub_blocks
            $foremanSubBlock = ForemanSubBlock::where('kode_petak', $validated['kode_petak'])
                ->first();

            if (!$foremanSubBlock) {
                throw new \Exception('Data mandor untuk petak ini tidak ditemukan.');
            }

            $updateData = [
                'kode_spt' => $validated['kode_spt'],
                'kode_vendor' => $validated['kode_vendor'],
                'kode_mandor' => $validated['kode_mandor'],
                'kode_petak' => $validated['kode_petak'],
                'tanggal_tebang' => $validated['tanggal_tebang'],
                'jumlah_tenaga_kerja' => $validated['jumlah_tenaga_kerja'],
                'jenis_tebang' => $validated['jenis_tebang'],
                'catatan' => $validated['catatan'] ?? null,
                'dibuat_oleh' => $validated['dibuat_oleh'],
                'diperiksa_oleh' => $validated['diperiksa_oleh'],
                'disetujui_oleh' => $validated['disetujui_oleh'],
                'status' => $spt->status ?? 'Draft',
            ];

            // Hanya update approval_stage jika belum ada atau masih draft
            if (empty($spt->approval_stage) || $spt->approval_stage === SPT::STAGE_DRAFT) {
                $updateData['approval_stage'] = SPT::STAGE_DRAFT;
            }

            $spt->update($updateData);

            // Clear all flash messages to prevent duplicates
            session()->forget(['success', 'message']);

            // Set success message that will be shown as toast
            $message = 'Surat Perintah Tebang berhasil diperbarui!';
            
            // Set flash data for toast notification
            session()->flash('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
                'position' => 'top-end',
                'timer' => 5000,
                'showConfirmButton' => false,
                'timerProgressBar' => true
            ]);
            
            // Redirect to show page
            return redirect()->route('spt.show', $spt->id);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SPT $spt)
    {
        try {
            // Delete signature files
            if ($spt->ttd_dibuat_oleh_path) {
                Storage::delete($spt->ttd_dibuat_oleh_path);
            }
            if ($spt->ttd_diperiksa_oleh_path) {
                Storage::delete($spt->ttd_diperiksa_oleh_path);
            }
            if ($spt->ttd_disetujui_oleh_path) {
                Storage::delete($spt->ttd_disetujui_oleh_path);
            }

            $spt->delete();

            // Set success message that will be shown as toast
            $message = 'Surat Perintah Tebang berhasil dihapus.';
            
            // Set flash data for toast notification
            session()->flash('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $message,
                'position' => 'top-end',
                'timer' => 5000,
                'showConfirmButton' => false,
                'timerProgressBar' => true
            ]);
            
            return redirect()->route('spt.index');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }



    /**
     * Get mandors for a specific date and block
     *
     * @param string $date The date in Y-m-d format
     * @param string $block The block code
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get mandors for a specific date and block
     *
     * @param string $date The date in Y-m-d format
     * @param string $block The block code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMandors($date, $block)
    {
        try {
            // Log the incoming request
            \Log::info('getMandors called', ['date' => $date, 'block' => $block]);

            // Validate input
            if (empty($block)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode petak tidak boleh kosong'
                ], 400);
            }

            // Get mandors for this block
            $mandors = \App\Models\ForemanSubBlock::where('kode_petak', $block)
                ->select('kode_mandor', 'nama_mandor')
                ->distinct()
                ->get()
                ->map(function($item) {
                    return [
                        'kode_mandor' => $item->kode_mandor,
                        'nama_mandor' => $item->nama_mandor
                    ];
                });

            \Log::debug('Mandors found:', ['count' => $mandors->count(), 'mandors' => $mandors]);

            return response()->json([
                'success' => true,
                'mandors' => $mandors
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getMandors:', [
                'date' => $date,
                'block' => $block,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data mandor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available vendors and petaks for a specific date
     *
     * @param string $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailabilityByDate($date)
    {
        \Log::info('getAvailabilityByDate called with date: ' . $date);
        try {
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new \Exception('Invalid date format. Expected YYYY-MM-DD');
            }
            $date = Carbon::parse($date)->format('Y-m-d');

            // Get all vendors with their SPT count for the selected date
            $vendors = Vendor::where('status', 'Aktif')
                ->where('kode_vendor', 'like', 'VT%')
                ->withCount(['spts as spts_count' => function($query) use ($date) {
                    $query->whereDate('tanggal_tebang', $date);
                }])
                ->get()
                ->map(function($vendor) {
                    return [
                        'id' => $vendor->kode_vendor,
                        'name' => $vendor->nama_vendor,
                        'spt_count' => $vendor->spts_count,
                    ];
                });

            // First, get all valid sub-blocks that have a matching harvest sub-block
            $subBlocks = SubBlock::whereIn('kode_petak', function($query) {
                    $query->select('kode_petak')->from('harvest_sub_blocks');
                })
                ->get()
                ->keyBy('kode_petak');

            // Get all SPTs for the selected date to calculate vendor counts per petak
            $spts = SPT::whereDate('tanggal_tebang', $date)
                ->select('kode_petak', 'kode_vendor')
                ->get()
                ->groupBy('kode_petak');

            // Then get only the harvest sub-blocks that have a matching sub-block
            $harvestSubBlocks = HarvestSubBlock::whereIn('kode_petak', $subBlocks->keys())
                ->get()
                ->map(function($subBlock) use ($date, $subBlocks, $spts) {
                    // Only process if we have a valid sub-block
                    if ($subBlocks->has($subBlock->kode_petak)) {
                        $sptCount = SPT::where('kode_petak', $subBlock->kode_petak)
                            ->whereDate('tanggal_tebang', $date)
                            ->count();

                        // Get unique vendor count for this petak on the selected date
                        $vendorCount = isset($spts[$subBlock->kode_petak])
                            ? $spts[$subBlock->kode_petak]->unique('kode_vendor')->count()
                            : 0;

                        $subBlock->spt_count = $sptCount;
                        $subBlock->vendor_count = $vendorCount;
                        $subBlock->blok = $subBlocks->get($subBlock->kode_petak)->blok;
                        return $subBlock;
                    }
                    return null;
                })
                ->filter() // Remove null entries
                ->values(); // Reset array keys

            $response = [
                'success' => true,
                'vendors' => $vendors,
                'harvest_sub_blocks' => $harvestSubBlocks->map(function($block) use ($subBlocks) {
                    $subBlock = $subBlocks->get($block->kode_petak);
                    return [
                        'kode_petak' => $block->kode_petak,
                        'blok' => $block->blok,
                        'spt_count' => $block->spt_count,
                        'luas_area' => $subBlock ? $subBlock->luas_area : 0,
                        'vendor_count' => $block->vendor_count ?? 0,
                    ];
                })
            ];

            \Log::info('getAvailabilityByDate response:', $response);
            return response()->json($response);
        } catch (\Exception $e) {
            $errorMessage = 'Error in getAvailabilityByDate: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            \Log::error($errorMessage);
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Log the exact error for debugging
            if ($e->getPrevious()) {
                \Log::error('Previous error: ' . $e->getPrevious()->getMessage());
            }

            $response = [
                'success' => false,
                'message' => 'Failed to fetch availability data',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];

            \Log::error('Error response:', $response);
            return response()->json($response, 500);
        }
    }

    public function export(Request $request)
    {
        $query = SPT::with(['vendor', 'harvestSubBlock', 'foreman']);

        // Apply filters if any
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_spt', 'like', "%{$search}%")
                  ->orWhere('jenis_tebang', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('nama_vendor', 'like', "%{$search}%");
                  });
            });
        }

        // Apply date filters if any
        if ($request->has('tanggal_mulai') && !empty($request->tanggal_mulai)) {
            $query->whereDate('tanggal_tebang', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && !empty($request->tanggal_selesai)) {
            $query->whereDate('tanggal_tebang', '<=', $request->tanggal_selesai);
        }

        // Apply status filter if any
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $spts = $query->orderBy('tanggal_tebang', 'desc')->get();

        // Set filename with current date
        $filename = 'spt_export_' . now()->format('Ymd_His') . '.xls';

        // Set headers for download
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Return the view with headers
        return response()->view('spt.export', compact('spts'))
            ->withHeaders($headers);
    }

    /**
     * Helper method to upload signature file.
     */
    private function uploadSignature($file, $directory)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/' . $directory, $filename);
        return str_replace('public/', '', $path);
    }
}
