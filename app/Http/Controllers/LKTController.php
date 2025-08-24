<?php

namespace App\Http\Controllers;

use App\Models\LKT;
use App\Models\SPT;
use App\Models\VendorAngkut;
use App\Models\SubBlock;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Exports\LKTExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LKTController extends Controller
{
    public function index(Request $request)
    {
        // Get current user role and username
        $user = auth()->user();
        $userRole = $user->role_name;
        $username = $user->username;

        // Log user info for debugging
        \Log::info('User accessing LKT index', [
            'user_id' => $user->id,
            'username' => $username,
            'role' => $userRole
        ]);

        $query = LKT::with(['spt', 'vendorTebang', 'vendorAngkut', 'petak', 'driver']);

        // Apply role-based filtering
        if ($userRole === 'PT PAG' || $userRole === 'Manager Finance' || $userRole === 'Manager Plantation' || $userRole === 'Assistant Manager CDR' || $userRole === 'Manager CDR') {
            // For PT PAG, we'll handle the filtering in the status filter section
            $query->where(function($q) {
                $q->where(function($q2) {
                    $q2->where('status', 'Disetujui')
                      ->where('approval_stage', \App\Models\LKT::STAGE_P3);
                })->orWhere('status', 'Selesai');
            });
        } elseif ($userRole === 'Assistant Manager Plantation' || $userRole === 'Director') {
            // For Assistant Manager Plantation, only show LKTs that have been signed by Pemeriksa 1
            $query->where('status', '!=', 'Draft')
                  ->whereNotNull('ttd_diperiksa_oleh_path')
                  ->where('approval_stage', '>=', \App\Models\LKT::STAGE_P2);
        } elseif (in_array($userRole, ['Assistant Divisi Plantation', 'Manager Plantation'])) {
            // Hide draft LKTs for these roles
            $query->where('status', '!=', 'Draft');
        } elseif ($userRole === 'mandor') {
            // Get mandor's kode_mandor from foreman
            $mandor = \App\Models\Foreman::where('email', $username)
                ->orWhere('kode_mandor', $username)
                ->first();
            
            if ($mandor) {
                // For Mandor, only show LKTs that are linked to SPTs assigned to them
                $kodeMandor = $mandor->kode_mandor;
                $query->whereHas('spt', function($q) use ($kodeMandor) {
                    $q->where('kode_mandor', $kodeMandor);
                });
                
                // Log the raw SQL query for debugging
                \Log::info('LKT Query for Mandor:', [
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
        } elseif ($userRole === 'vendor') {
            // Get vendor's phone number from vendor_angkut table
            $vendor = \App\Models\VendorAngkut::where('no_hp', $username)
                ->orWhere('kode_vendor', $username)
                ->first();
            
            if ($vendor) {
                // For Vendor, only show LKTs where they are either the vendor_tebang or vendor_angkut
                $kodeVendor = $vendor->kode_vendor;
                $query->where(function($q) use ($kodeVendor) {
                    $q->where('kode_vendor_tebang', $kodeVendor)
                      ->orWhere('kode_vendor_angkut', $kodeVendor);
                });
                
                // Log the raw SQL query for debugging
                \Log::info('LKT Query for Vendor:', [
                    'username' => $username,
                    'kode_vendor' => $kodeVendor,
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);
            } else {
                // If no vendor found, return empty result
                $query->whereRaw('1=0');
                \Log::warning('No vendor found for user: ' . $username);
            }
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_lkt', 'like', "%{$search}%")
                  ->orWhere('kode_spt', 'like', "%{$search}%")
                  ->orWhereHas('vendorTebang', function($q) use ($search) {
                      $q->where('nama_vendor', 'like', "%{$search}%")
                        ->orWhere('kode_vendor', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendorAngkut', function($q) use ($search) {
                      $q->where('nama_vendor', 'like', "%{$search}%")
                        ->orWhere('kode_vendor', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function($q) use ($search) {
                      $q->where('nama_vendor', 'like', "%{$search}%")
                        ->orWhere('kode_lambung', 'like', "%{$search}%")
                        ->orWhere('plat_nomor', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->has('tanggal_mulai') && !empty($request->tanggal_mulai)) {
            $query->whereDate('tanggal_tebang', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && !empty($request->tanggal_selesai)) {
            $query->whereDate('tanggal_tebang', '<=', $request->tanggal_selesai);
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            if ($userRole === 'PT PAG') {
                // For PT PAG, modify the existing query to filter by the selected status
                $query->where(function($q) use ($request) {
                    if ($request->status === 'Waiting') {
                        $q->where('status', 'Disetujui')
                          ->where('approval_stage', LKT::STAGE_P3);
                    } elseif ($request->status === 'Selesai') {
                        $q->where('status', 'Selesai');
                    }
                });
            } elseif ($request->status === 'Waiting' && $userRole === 'Assistant Divisi Plantation') {
                // For Assistant Divisi Plantation, show LKTs that are in 'Diajukan' status and the user is the next approver
                $query->where('status', 'Diajukan')
                    ->where('approval_stage', LKT::STAGE_P1);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Use paginate instead of get()
        $lkts = $query->latest()->paginate(15);
        
        return view('lkt.index', compact('lkts'));
    }

    public function create()
{
    // Ambil semua SPT yang status-nya sudah "Disetujui" dan belum "COMPLETED"
    $spts = SPT::where('status', 'Disetujui')
              ->where('status', '!=', 'COMPLETED')
              ->get();

    // Ambil vendor angkut aktif dengan jenis_vendor = 'Vendor Angkut'
    $vendors = VendorAngkut::where('status', 'Aktif')
        ->where('jenis_vendor', 'Vendor Angkut')
        ->get();

    // Ambil semua kendaraan (ambil kode lambung)
    $drivers = Vehicle::select('kode_lambung', 'plat_nomor', 'nama_vendor')->get();

    // Generate kode LKT otomatis berurutan
    $lastKode = LKT::latest('id')->first()?->kode_lkt;
    $nextNumber = $lastKode ? ((int) filter_var($lastKode, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;
    $kodeLKT = 'LKT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    return view('lkt.create', compact('spts', 'vendors', 'drivers', 'kodeLKT'));
}


    public function store(Request $request)
{
    $request->validate([
        'kode_lkt' => 'required|unique:lkt,kode_lkt',
        'tanggal_tebang' => 'required|date',
        'kode_spt' => 'required',
        'kode_vendor_tebang' => 'required|exists:vendor_angkut,kode_vendor',
        'kode_vendor_angkut' => 'nullable|exists:vendor_angkut,kode_vendor',
        'kode_driver' => 'nullable',
        'kode_petak' => 'required',
        'tarif_zona_angkutan' => 'nullable|integer|min:1|max:4',
        'jenis_tebangan' => 'required|string',
    ]);

    $lkt = LKT::create([
        'kode_lkt' => $request->kode_lkt,
        'tanggal_tebang' => $request->tanggal_tebang,
        'kode_spt' => $request->kode_spt,
        'kode_vendor_tebang' => $request->kode_vendor_tebang, // Now using the code directly
        'kode_vendor_angkut' => $request->kode_vendor_angkut,
        'kode_driver' => $request->kode_driver,
        'kode_petak' => $request->kode_petak,
        'tarif_zona_angkutan' => $request->tarif_zona_angkutan ?? '1',
        'jenis_tebangan' => $request->jenis_tebangan,
        'dibuat_oleh' => $request->dibuat_oleh ?? 'System',
        'diperiksa_oleh' => $request->diperiksa_oleh ?? null,
        'disetujui_oleh' => $request->disetujui_oleh ?? null,
        'ditimbang_oleh' => $request->ditimbang_oleh ?? null,
        'catatan' => $request->catatan,
        'status' => 'Draft',
    ]);

    return redirect()->route('lkt.show', $lkt->id)->with('success', 'LKT berhasil ditambahkan.');
}


    public function show($id)
{
    $lkt = LKT::with([
        'vendorTebang',
        'vendorAngkut',
        'driver',
        'petak',
        'statusPetak'
    ])->findOrFail($id);

    return view('lkt.show', compact('lkt'));
}


    public function edit($id)
{
    $lkt = LKT::findOrFail($id);

    // Prevent editing if status is not 'Draft'
    if ($lkt->status !== 'Draft') {
        return redirect()->route('lkt.index')->with('error', 'LKT tidak dapat diedit karena status sudah ' . $lkt->status);
    }

    // Ambil hanya SPT yang sudah disetujui
    $spts = SPT::where('status', 'Disetujui')->get();

    // Ambil vendor angkut aktif
    $vendors = VendorAngkut::where('status', 'Aktif')
        ->where('jenis_vendor', 'Vendor Angkut')
        ->get();

    // Ambil kendaraan (ambil kode lambung)
    $drivers = Vehicle::select('kode_lambung', 'plat_nomor', 'nama_vendor')->get();

    return view('lkt.edit', compact('lkt', 'spts', 'vendors', 'drivers'));
}


    public function update(Request $request, $id)
{
    $request->validate([
        'kode_lkt' => 'required|unique:lkt,kode_lkt,' . $id,
        'tanggal_tebang' => 'required|date',
        'kode_spt' => 'required',
        'kode_vendor_tebang' => 'required',
        'kode_vendor_angkut' => 'nullable',
        'kode_driver' => 'nullable',
        'tarif_zona_angkutan' => 'nullable|in:1,2,3,4',
        'kode_petak' => 'required',
        'catatan' => 'nullable|string'
    ]);

    $lkt = LKT::findOrFail($id);

    // Jika status sebelumnya adalah "Diajukan", ubah ke Draft
    if ($lkt->status === 'Diajukan') {
        $request->merge(['status' => 'Draft']);
    }

    $lkt->update($request->all());

    return redirect()->route('lkt.index')->with('success', 'LKT berhasil diperbarui');
}


    public function destroy($id)
    {
        LKT::destroy($id);
        return redirect()->route('lkt.index')->with('success', 'LKT berhasil dihapus.');
    }

    public function getSPTData($kode)
    {
        try {
            $spt = SPT::with(['vendor', 'subBlock'])
                ->where('kode_spt', $kode)
                ->where('status', 'Disetujui')
                ->first();

            if (!$spt) {
                return response()->json([
                    'success' => false,
                    'message' => 'SPT tidak ditemukan atau belum disetujui'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'kode_vendor_tebang' => $spt->kode_vendor ?? '',
                'nama_vendor_tebang' => $spt->vendor->nama_vendor ?? 'Vendor tidak ditemukan',
                'kode_petak' => $spt->kode_petak ?? '',
                'tarif_zona_angkutan' => $spt->subBlock->zona ?? '1',
                'jenis_tebangan' => $spt->jenis_tebang ?? 'Tebang Biasa'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Save signature to storage
     *
     * @param string $signature Base64 encoded signature image
     * @param string $path Storage path (default: 'signatures')
     * @param string|null $filename Optional custom filename (without extension)
     * @return string Path to the saved signature
     * @throws \Exception
     */
    private function saveSignature($signature, $path = 'signatures', $filename = null)
    {
        try {
            $image = str_replace('data:image/png;base64,', '', $signature);
            $image = str_replace(' ', '+', $image);
            
            // Generate a unique filename if not provided
            $imageName = $filename ?: 'signature_' . Str::random(10);
            if (!Str::endsWith(strtolower($imageName), '.png')) {
                $imageName .= '.png';
            }
            
            // Ensure the directory exists
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path, 0755, true);
            }
            
            // Save the file
            $filePath = $path . '/' . $imageName;
            Storage::disk('public')->put($filePath, base64_decode($image));
            
            return $filePath;
        } catch (\Exception $e) {
            \Log::error('Error saving signature: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update LKT status with signature
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diajukan,Diperiksa,Disetujui,Ditolak,Selesai',
            'signature' => 'required|string',
            'catatan' => 'nullable|string|max:1000'
        ]);

        $lkt = LKT::findOrFail($id);
        $user = auth()->user();
        
        // Validate if user can perform this action
        if (!$lkt->canBeApprovedBy($user)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini');
        }
        
        // Save signature
        $signaturePath = $this->saveSignature($request->signature);
        if (!$signaturePath) {
            return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan');
        }
        
        // Update status and signature based on current stage
        $updateData = [
            'catatan' => $request->catatan
        ];
        
        // Handle status transition
        switch ($lkt->approval_stage) {
            case LKT::STAGE_DRAFT:
                $updateData['status'] = LKT::STATUS_DIAJUKAN;
                $updateData['approval_stage'] = LKT::STAGE_P1;
                $updateData['ttd_dibuat_oleh_path'] = $signaturePath;
                $updateData['dibuat_oleh'] = $user->name;
                break;
                
            case LKT::STAGE_P1:
                if ($request->status === 'Ditolak') {
                    $updateData['status'] = LKT::STATUS_DITOLAK;
                    $updateData['approval_stage'] = LKT::STAGE_DRAFT;
                } else {
                    $updateData['status'] = LKT::STATUS_DIPERIKSA;
                    $updateData['approval_stage'] = LKT::STAGE_P2;
                }
                $updateData['ttd_diperiksa_oleh_path'] = $signaturePath;
                $updateData['diperiksa_oleh'] = $user->name;
                break;
                
            case LKT::STAGE_P2:
                if ($request->status === 'Ditolak') {
                    $updateData['status'] = LKT::STATUS_DITOLAK;
                    $updateData['approval_stage'] = LKT::STAGE_P1;
                } else {
                    $updateData['status'] = LKT::STATUS_DISETUJUI;
                    $updateData['approval_stage'] = LKT::STAGE_P3;
                }
                $updateData['ttd_disetujui_oleh_path'] = $signaturePath;
                $updateData['disetujui_oleh'] = $user->name;
                break;
                
            case LKT::STAGE_P3:
                $updateData['status'] = LKT::STATUS_SELESAI;
                $updateData['approval_stage'] = LKT::STAGE_COMPLETED;
                $updateData['ttd_ditimbang_oleh_path'] = $signaturePath;
                $updateData['ditimbang_oleh'] = $user->name;
                break;
        }
        
        $lkt->update($updateData);
        
        return redirect()->route('lkt.show', $lkt->id)
            ->with('success', 'Status LKT berhasil diperbarui');
    }

    /**
     * Display a listing of LKTs for approval
     */
    public function approvalIndex()
    {
        $lkts = LKT::whereIn('status', ['Diajukan', 'Diperiksa', 'Disetujui'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('lkt.approval.index', compact('lkts'));
    }

    public function approvalShow($id)
    {
        $lkt = LKT::with(['spt', 'vendorTebang', 'vendorAngkut', 'petak', 'driver'])
            ->findOrFail($id);
            
        return view('lkt.approval.show', compact('lkt'));
    }

    /**
     * Check if LKT exists in hasil_tebang
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTimbangan($id)
    {
        $lkt = LKT::findOrFail($id);
        $exists = \App\Models\HasilTebang::where('kode_lkt', $lkt->kode_lkt)->exists();
        
        return response()->json([
            'exists' => $exists,
            'kode_lkt' => $lkt->kode_lkt
        ]);
    }

    /**
     * Generate PDF for the specified LKT.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf($id)
    {
        $lkt = LKT::with(['spt', 'vendorTebang', 'vendorAngkut', 'petak', 'driver'])->findOrFail($id);

        // Generate PDF
        $pdf = PDF::loadView('lkt.pdf', compact('lkt'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Download the PDF with a custom filename
        return $pdf->download('LKT-' . $lkt->kode_lkt . '.pdf');
    }

    /**
     * Approve LKT
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, $id)
    {
        $lkt = LKT::findOrFail($id);
        $user = auth()->user();
        
        $request->validate([
            'signature' => 'required|string',
        ]);

        // Save the signature
        $signature = $this->saveSignature($request->signature, "lkt/signatures");

        // If status is 'Disetujui' (already approved by P2), then this is P3 (weighing stage)
        if ($lkt->status === 'Disetujui' && $lkt->approval_stage === LKT::STAGE_P3) {
            // For PT PAG user, check if the LKT has been added to Hasil Tebangan
            if ($user->role_name === 'PT PAG') {
                $hasTimbangan = \App\Models\HasilTebang::where('kode_lkt', $lkt->kode_lkt)->exists();
                
                if (!$hasTimbangan) {
                    return redirect()->back()
                        ->with('toast', [
                            'type' => 'error',
                            'title' => 'Gagal',
                            'text' => 'Tidak dapat menandatangani LKT karena nomor LKT ' . $lkt->kode_lkt . ' belum ditambahkan di Hasil Tebangan.',
                            'showConfirmButton' => true,
                            'confirmButtonText' => 'Mengerti',
                            'timer' => 10000
                        ]);
                }
                
                // Update LKT with weighing signature and complete the process
                $lkt->update([
                    'ttd_ditimbang_oleh_path' => $signature,
                    'ditimbang_oleh' => $user->name,
                    'ttd_ditimbang_pada' => now(),
                    'status' => 'Selesai',
                    'approval_stage' => LKT::STAGE_COMPLETED
                ]);
                
                $message = 'LKT telah berhasil ditandatangani dan dokumen telah selesai diproses.';
            } else {
                // Handle other admin users if needed
                $lkt->update([
                    'ttd_ditimbang_oleh_path' => $signature,
                    'ditimbang_oleh' => $user->name,
                    'ttd_ditimbang_pada' => now(),
                    'status' => 'Selesai',
                    'approval_stage' => LKT::STAGE_COMPLETED
                ]);
                
                $message = 'LKT telah berhasil ditandatangani dan dokumen telah selesai diproses.';
            }
        }
        // Jika status saat ini 'Diperiksa' (sudah disetujui P1), maka ini P2
        elseif ($lkt->status === 'Diperiksa') {
            $lkt->update([
                'ttd_disetujui_oleh_path' => $signature,
                'ttd_disetujui_oleh' => auth()->user()->name,
                'ttd_disetujui_pada' => now(),
                'status' => 'Disetujui'  // Update status to 'Disetujui' after P2 approval
            ]);
            
            $message = 'LKT berhasil disetujui dan menunggu tanda tangan petugas timbangan';
        } else {
            // Ini untuk P1
            $lkt->update([
                'ttd_diperiksa_oleh_path' => $signature,
                'diperiksa_oleh' => auth()->user()->name,
                'ttd_diperiksa_pada' => now(),
                'status' => 'Diperiksa'  // Update status to 'Diperiksa' after P1 approval
            ]);
            
            $message = 'LKT berhasil disetujui dan diteruskan ke Pemeriksa 2';
        }

        return redirect()->route('lkt.approval.index')
            ->with('success', $message);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|min:10',
        ]);

        $lkt = LKT::findOrFail($id);
        
        $lkt->update([
            'status' => 'Ditolak',
            'alasan_penolakan' => $request->alasan_penolakan,
            'ditolak_oleh' => auth()->id(),
            'ditolak_pada' => now()
        ]);

        return redirect()->route('lkt.approval.index')
            ->with('success', 'LKT berhasil ditolak');
    }

    public function signAndSubmit(Request $request, $id)
    {
        $lkt = LKT::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'signature_data' => 'required|string',
        ]);

        try {
            // Save the signature
            $filename = 'signature_dibuat_oleh_' . $lkt->id . '_' . time();
            $signaturePath = $this->saveSignature(
                $request->signature_data,
                'signatures/lkt',
                $filename
            );
            
            $lkt->update([
                'ttd_dibuat_oleh_path' => $signaturePath,
                'dibuat_oleh' => $user->name,
                'ttd_dibuat_pada' => now(),
                'status' => 'Diajukan',
                'approval_stage' => LKT::STAGE_P1
            ]);

            return redirect()->route('lkt.show', $lkt->id)
                ->with('success', 'Tanda tangan berhasil disimpan dan LKT telah diajukan.');
                
        } catch (\Exception $e) {
            \Log::error('Error in signAndSubmit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
        }
    }

    public function sign(Request $request, $id)
    {
        $lkt = LKT::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'signature_type' => 'required|in:dibuat_oleh,diperiksa_oleh,disetujui_oleh,ditimbang_oleh',
            'signature_data' => 'required|string|starts_with:data:image/'
        ]);

        $signatureType = $request->signature_type;
        
        try {
            // Generate a descriptive filename
            $filename = 'signature_' . $signatureType . '_' . $lkt->id . '_' . time();
            
            // Save the signature
            $filePath = $this->saveSignature(
                $request->signature_data,
                'signatures/lkt',
                $filename
            );

            // Update the LKT record with the signature path and signer's name
            $updateData = [
                'ttd_' . $signatureType . '_path' => $filePath,
                $signatureType => $user->name,
                $signatureType . '_at' => now()
            ];

            // If this is a review/approval signature, update status accordingly
            if ($signatureType === 'diperiksa_oleh') {
                $updateData['status'] = 'Diperiksa';
                $updateData['approval_stage'] = LKT::STAGE_P2;
            } elseif ($signatureType === 'disetujui_oleh') {
                $updateData['status'] = 'Disetujui';
                $updateData['approval_stage'] = LKT::STAGE_P3;
            } elseif ($signatureType === 'ditimbang_oleh') {
                $updateData['status'] = 'Selesai';
                $updateData['approval_stage'] = LKT::STAGE_COMPLETED;
            }

            $lkt->update($updateData);

            return redirect()->back()
                ->with('success', 'Tanda tangan berhasil disimpan.');

        } catch (\Exception $e) {
            \Log::error('Error in sign: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
        }
    }

    public function timbangan(Request $request, $id)
    {
        $lkt = LKT::findOrFail($id);
        
        // Validate the request
        $request->validate([
            'signature' => 'required|string',
        ]);

        $signatureType = 'ditimbang_oleh';
        $directory = 'lkt/signatures';
        
        // Generate a unique filename
        $filename = 'signature_' . $signatureType . '_' . $lkt->id . '_' . time() . '.png';
        $filePath = $directory . '/' . $filename;

        // Save the signature image
        $image = str_replace('data:image/png;base64,', '', $request->signature);
        $image = str_replace(' ', '+', $image);
        Storage::disk('public')->put($filePath, base64_decode($image));

        // Update the LKT record with the signature path and signer's name
        $lkt->update([
            'ttd_' . $signatureType . '_path' => $filePath,  
            $signatureType => auth()->user()->name,
            'status' => 'Selesai',
            'ttd_ditimbang_pada' => now()
        ]);

        return redirect()->route('lkt.approval.index')
            ->with('success', 'Tanda tangan petugas timbangan berhasil disimpan dan dokumen telah selesai diproses.');
    }

public function exportExcel(Request $request)
{
    $query = LKT::with(['vendorTebang', 'vendorAngkut', 'driver']);

    // Apply search filter
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('kode_lkt', 'like', "%{$search}%")
              ->orWhere('kode_spt', 'like', "%{$search}%")
              ->orWhereHas('vendorTebang', function($q) use ($search) {
                  $q->where('nama_vendor', 'like', "%{$search}%")
                    ->orWhere('kode_vendor', 'like', "%{$search}%");
              })
              ->orWhereHas('vendorAngkut', function($q) use ($search) {
                  $q->where('nama_vendor', 'like', "%{$search}%")
                    ->orWhere('kode_vendor', 'like', "%{$search}%");
              })
              ->orWhereHas('driver', function($q) use ($search) {
                  $q->where('nama_vendor', 'like', "%{$search}%")
                    ->orWhere('kode_lambung', 'like', "%{$search}%")
                    ->orWhere('plat_nomor', 'like', "%{$search}%");
              });
        });
    }

    // Apply date range filter
    if ($request->has('tanggal_mulai') && !empty($request->tanggal_mulai)) {
        $query->whereDate('tanggal_tebang', '>=', $request->tanggal_mulai);
    }

    if ($request->has('tanggal_selesai') && !empty($request->tanggal_selesai)) {
        $query->whereDate('tanggal_tebang', '<=', $request->tanggal_selesai);
    }

    // Apply status filter
    if ($request->has('status') && !empty($request->status)) {
        $query->where('status', $request->status);
    }

    $lkts = $query->orderBy('tanggal_tebang', 'desc')->get();

    // Set filename with current date
    $filename = 'lkt_export_' . now()->format('Ymd_His') . '.xls';

    // Set headers for download
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    // Return the view with headers
    return response()->view('lkt.export', compact('lkts'))
        ->withHeaders($headers);
}

}
