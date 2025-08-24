<?php

namespace App\Http\Controllers;

use App\Models\SPT;
use App\Models\User; // Pastikan model User di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ApprovalSPTController extends Controller
{
    /**
     * Display a listing of SPTs pending approval.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get SPTs that need approval (status 'Diajukan' or 'Draft')
        $spts = SPT::with(['vendor', 'subBlock', 'foremanSubBlock'])
            ->whereIn('status', ['Diajukan', 'Draft'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('spt.approval.index', compact('spts'));
    }

    /**
     * Show the form for approving/rejecting the specified SPT.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $spt = SPT::with(['vendor', 'subBlock', 'foremanSubBlock'])->findOrFail($id);
        $user = auth()->user();

        // Check if user has permission to view this SPT
        if (!$spt->canBeSignedBy($user) && !$user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk melihat halaman ini.');
        }

        return view('spt.approval.show', compact('spt', 'user'));
    }

    /**
     * Update the specified SPT's approval status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, $id)
    {
        try {
            $spt = SPT::findOrFail($id);
            $user = Auth::user();

            // Validate request
            $validated = $request->validate([
                'status' => 'required|in:Diajukan,Diperiksa,Disetujui,Selesai,Ditolak',
                'signature' => 'required_if:status,Diajukan,Diperiksa,Disetujui',
            ]);

            // Log request data for debugging
            Log::info('Approve request data:', [
                'spt_id' => $id,
                'status' => $validated['status'],
                'has_signature' => $request->has('signature') && !empty($request->input('signature')),
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);

            // Save signature if provided
            $signaturePath = null;
            if ($request->has('signature') && $request->input('signature')) {
                $signaturePath = $this->saveSignature($request->input('signature'), $spt->approval_stage);
                if (!$signaturePath) {
                    Log::error('Gagal menyimpan tanda tangan', [
                        'spt_id' => $id,
                        'user_id' => $user->id
                    ]);
                    return back()->with('error', 'Gagal menyimpan tanda tangan. Silakan coba lagi.');
                }
                Log::info('Tanda tangan berhasil disimpan', [
                    'spt_id' => $id,
                    'path' => $signaturePath
                ]);
            }

            // Update SPT based on status
            switch ($validated['status']) {
                case 'Diajukan':
                    $spt->ttd_dibuat_oleh_path = $signaturePath;
                    $spt->dibuat_oleh = $user->name;
                    $spt->status = 'Diajukan';
                    $spt->approval_stage = SPT::STAGE_PEMBUAT;
                    $message = 'SPT berhasil diajukan.';
                    break;

                case 'Diperiksa':
                    $spt->ttd_diperiksa_oleh_path = $signaturePath;
                    $spt->diperiksa_oleh = $user->name;
                    $spt->status = 'Diperiksa';
                    $spt->approval_stage = SPT::STAGE_PEMERIKSA;
                    $message = 'SPT berhasil diperiksa.';
                    break;

                case 'Disetujui':
                    $spt->ttd_disetujui_oleh_path = $signaturePath;
                    $spt->disetujui_oleh = $user->name;
                    $spt->status = 'Disetujui';
                    $spt->approval_stage = SPT::STAGE_PENYETUJU;
                    $message = 'SPT berhasil disetujui.';
                    break;

                case 'Selesai':
                    $spt->status = 'Selesai';
                    $spt->approval_stage = SPT::STAGE_SELESAI;
                    $message = 'SPT berhasil ditandai sebagai selesai.';
                    break;
                    
                case 'Ditolak':
                    $spt->status = 'Ditolak';
                    $spt->approval_stage = SPT::STAGE_DITOLAK;
                    $message = 'SPT telah ditolak.';
                    break;
            }

            $spt->save();

            Log::info('SPT berhasil diupdate', [
                'spt_id' => $spt->id,
                'status' => $spt->status,
                'approval_stage' => $spt->approval_stage
            ]);

            // Handle AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => Auth::user()->hasRole('admin') 
                        ? route('approval.spt.index')
                        : route('spt.show', $spt->id)
                ]);
            }

            // Redirect based on user role for non-AJAX requests
            if (Auth::user()->hasRole('admin')) {
                return redirect()->route('approval.spt.index')
                    ->with('success', $message);
            }

            return redirect()->route('spt.show', $spt->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error in approve method:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.');
        }
    }

    /**
     * Save the signature image to storage.
     *
     * @param  string  $signatureData
     * @param  string  $type
     * @return string|null
     */
    private function saveSignature($signatureData, $type = 'draft')
    {
        try {
            // Validasi data base64
            if (empty($signatureData)) {
                throw new \Exception('Data tanda tangan kosong');
            }

            // Hapus header base64 jika ada
            if (strpos($signatureData, ';base64,') !== false) {
                $signatureData = explode(';base64,', $signatureData)[1];
            }
            
            // Decode base64
            $decodedImage = base64_decode($signatureData);
            if ($decodedImage === false) {
                throw new \Exception('Format base64 tidak valid');
            }
            
            // Generate nama file unik
            $filename = Str::random(40) . '.png';
            $relativePath = 'signatures/' . strtolower(trim($type, '/')) . '/' . $filename;
            
            // Simpan file ke storage
            Storage::disk('public')->put($relativePath, $decodedImage);
            
            // Kembalikan path relatif untuk disimpan di database
            return $relativePath;
        } catch (\Exception $e) {
            \Log::error('Error saving signature: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return null;
        }
    }
}
