/**
     * Update SPD status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diajukan,Diverifikasi,Disetujui,Ditolak,Selesai'
        ]);

        $spd = SPD::findOrFail($id);
        
        // Additional validation based on current status
        if ($spd->status === 'Draft' && $request->status === 'Diajukan') {
            // When submitting from Draft to Diajukan, ensure signature exists
            if (!$spd->ttd_dibuat_oleh) {
                return redirect()->back()->with('error', 'Tanda tangan pembuat (Mgr. Plantation) diperlukan sebelum mengajukan');
            }
        }

        $spd->update([
            'status' => $request->status,
            'status_updated_at' => now(),
            'status_updated_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Status SPD berhasil diperbarui');
    }

    /**
     * Display a listing of LKTs for approval
     */
    public function approvalIndex()
    {
        $spds = SPD::whereIn('status', ['Diajukan', 'Diverifikasi', 'Diperiksa', 'Disetujui'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('spd.approval.index', compact('spds'));
    }

    public function approve(Request $request, $id)
    {
        $spd = SPD::findOrFail($id);
        
        $request->validate([
            'signature' => 'required|string',
        ]);

        // Simpan tanda tangan
        $signature = $this->saveSignature($request->signature, "spd/signatures");

        // Jika status saat ini 'Disetujui' (sudah disetujui P3), maka ini P4
        if ($spd->status === 'Disetujui') {
            $lkt->update([
                'ttd_dibayar_oleh' => $signature,
                'ttd_dibayar_oleh' => auth()->user()->name,
                'ttd_dibayar_pada' => now(),
                'status' => 'Selesai'  // Update status to 'Selesai' after P4 approval
            ]);
            
            $message = 'Dana telah dibayarkan dan dokumen telah selesai diproses.';
        }
       
        // Jika status saat ini 'Diverifikasi' (sudah disetujui P2), maka ini P3
        elseif ($lkt->status === 'Diverifikasi') {
            $lkt->update([
                'ttd_disetujui_oleh_path' => $signature,
                'ttd_disetujui_oleh' => auth()->user()->name,
                'ttd_disetujui_pada' => now(),
                'status' => 'Disetujui'  // Update status to 'Disetujui' after P2 approval
            ]);
            
            $message = 'LKT berhasil disetujui dan menunggu tanda tangan petugas timbangan';

        } else {
            // Ini untuk P2
            $spd->update([
                'ttd_diketahui_oleh' => $signature,
                'ttd_diketahui_oleh' => auth()->user()->name,
                'ttd_diketahui_pada' => now(),
                'status' => 'Diverifikasi'  // Update status to 'Diverifikasi' after P2 approval
            ]);
            
            $message = 'SPD sudah diperiksa dan diteruskan untuk verifikasi';
        }
        } else {
            // Ini untuk P1
            $spd->update([
                'ttd_diverifikasi_oleh' => $signature,
                'ttd_diverifikasi_oleh' => auth()->user()->name,
                'ttd_diverifikasi_pada' => now(),
                'status' => 'Diperiksa'  // Update status to 'Diperiksa' after P1 approval
            ]);
            
            $message = 'SPD sudah diperiksa dan diteruskan untuk verifikasi';
        }

        return redirect()->route('spd.approval.index')
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

    private function saveSignature($signature, $path)
    {
        try {
            $image = str_replace('data:image/png;base64,', '', $signature);
            $image = str_replace(' ', '+', $image);
            $imageName = 'signature_' . time() . '.png';
            
            // Ensure the directory exists
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path, 0755, true);
            }
            
            // Save the file
            Storage::disk('public')->put($path . '/' . $imageName, base64_decode($image));
            
            // Return the full public path for storage in database
            return $path . '/' . $imageName;
        } catch (\Exception $e) {
            \Log::error('Error saving signature: ' . $e->getMessage());
            throw $e; // Re-throw to handle in the controller
        }
    }

    public function signAndSubmit(Request $request, $id)
{
    $lkt = LKT::findOrFail($id);

    $request->validate([
        'signature_data' => 'required|string',
    ]);

    if ($request->has('signature_data')) {
        $signatureData = $request->input('signature_data');
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        $fileName = 'signatures/' . Str::random(10) . '.png';
        Storage::disk('public')->put($fileName, base64_decode($image));
        $lkt->ttd_dibuat_oleh_path = $fileName;
    }

    $lkt->status = 'Diajukan';
    $lkt->save();

    return redirect()->route('lkt.show', $lkt->id)->with('success', 'Tanda tangan berhasil disimpan dan LKT telah diajukan.');
}

public function sign(Request $request, $id)
{
    $lkt = LKT::findOrFail($id);
        
    // Validate the request
    $request->validate([
        'signature_type' => 'required|in:dibuat_oleh,diperiksa_oleh,disetujui_oleh,ditimbang_oleh',
        'signature_data' => 'required|string|starts_with:data:image/'
    ]);

    $signatureType = $request->signature_type;
    $signatureData = $request->signature_data;

    try {
        // Create signatures directory if it doesn't exist
        $directory = 'signatures/lkt';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }

        // Generate a unique filename
        $filename = 'signature_' . $signatureType . '_' . $lkt->id . '_' . time() . '.png';
        $filePath = $directory . '/' . $filename;

        // Save the signature image
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        Storage::disk('public')->put($filePath, base64_decode($image));

        // Update the LKT record with the signature path and signer's name
        $lkt->update([
            'ttd_' . $signatureType . '_path' => $filePath,
            $signatureType => auth()->user()->name // Save the name of the person who signed
        ]);

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan.');

    } catch (\Exception $e) {
        \Log::error('Error saving signature: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
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