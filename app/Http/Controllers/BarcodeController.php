<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    /**
     * Display the specified barcode.
     */
    public function show($id = null)
    {
        // If no ID is provided, check if user is logged in
        if (!$id) {
            if (auth()->check()) {
                // Redirect to the logged-in user's barcode
                return redirect()->route('barcode.show', auth()->id());
            }
            // If not logged in, show error
            return back()->with('error', 'Please specify a user ID or log in to view barcode.');
        }

        // Load user with vendor relationship using vendor_id
        $user = User::with('vendor')->findOrFail($id);
        
        // Generate barcode data
        $barcodeData = $this->generateBarcodeData($user);
        
        return view('users.barcode', $barcodeData);
    }
    
    /**
     * Download barcode as PDF
     */
    public function download($id)
    {
        try {
            $user = User::with('vendor')->findOrFail($id);
            $barcodeData = $this->generateBarcodeData($user);
            
            // Generate HTML content directly with inline styles
            $html = view('pdf.barcode', $barcodeData)->render();
            
            // Create PDF with minimal options
            $pdf = PDF::loadHTML($html);
            
            // Set PDF options for better performance
            $pdf->setPaper('a4', 'portrait')
                ->setOption('isPhpEnabled', true)
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false) // Disable remote content
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('defaultFont', 'Helvetica')
                ->setOption('debugCss', false)
                ->setOption('debugKeepTemp', false)
                ->setOption('isPhpEnabled', true)
                ->setWarnings(false);
            
            $filename = 'barcode-vendor-' . ($user->vendor->kode_vendor ?? $user->id) . '.pdf';
            
            // Increase time limit for PDF generation
            set_time_limit(120); // 2 minutes
            
            // Generate and return the PDF
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal mengunduh barcode. ' . $e->getMessage());
        }
    }
    
    /**
     * Generate barcode data for display
     */
    protected function generateBarcodeData($user)
    {
        // If user has no vendor relation, try to find vendor by vendor_id
        if (!$user->vendor && $user->vendor_id) {
            $user->load('vendor');
        }

        // If still no vendor, try to find in vendor_angkut table
        if (!$user->vendor) {
            // Debug: Log the search criteria
            \Log::info('Searching for vendor', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name
            ]);

            // Get all vendors for debugging
            $allVendors = Vendor::where('kode_vendor', 'LIKE', 'VA%')
                ->orWhere('kode_vendor', 'LIKE', 'VT%')
                ->get(['id', 'kode_vendor', 'nama_vendor']);
                
            \Log::info('Available vendors', ['vendors' => $allVendors->toArray()]);

            // Find all vendor codes for this vendor by matching nama_vendor
            $vendors = [];
            if ($user->name) {
                $vendors = Vendor::where('nama_vendor', 'LIKE', '%' . $user->name . '%')
                    ->where(function($query) {
                        $query->where('kode_vendor', 'LIKE', 'VA%')
                              ->orWhere('kode_vendor', 'LIKE', 'VT%');
                    })
                    ->orderBy('kode_vendor', 'asc')
                    ->get();
                
                if ($vendors->isNotEmpty()) {
                    \Log::info('Found vendors by name match', [
                        'name' => $user->name,
                        'count' => $vendors->count(),
                        'vendor_codes' => $vendors->pluck('kode_vendor')
                    ]);
                }
            }
                
            if ($vendors->isNotEmpty()) {
                // Use the first vendor as primary, but include all vendor codes
                $primaryVendor = $vendors->first();
                $primaryVendor->all_kode_vendor = $vendors->pluck('kode_vendor')->implode(', ');
                $user->setRelation('vendor', $primaryVendor);
                \Log::info('Vendors found', ['kode_vendors' => $primaryVendor->all_kode_vendor]);
            } else {
                \Log::warning('No vendors found for user', ['user_id' => $user->id]);
                // Show barcode with user data if no vendors found
                $user->setRelation('vendor', (object)[
                    'id' => $user->id,
                    'kode_vendor' => 'N/A',
                    'all_kode_vendor' => 'N/A',
                    'nama_vendor' => $user->name,
                    'no_hp' => null,
                    'jenis_vendor' => null,
                    'status' => null,
                    'nomor_rekening' => null,
                    'nama_bank' => null
                ]);
            }
        }
        
        // Generate a simple URL with the vendor ID and code
        $code = $this->generateBarcodeCode($user);
        $url = url('/barcode/' . $user->id . '?code=' . $code);
        
        // Ensure kode_vendor is set
        $user->kode_vendor = $user->vendor->kode_vendor;
        
        // Generate QR Code as SVG for better compatibility
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(300)
            ->style('round')
            ->eye('circle')
            ->generate($url);
            
        // For web view, add width and height to SVG
        $qrCodeForWeb = str_replace(
            '<svg', 
            '<svg width="300" height="300"', 
            $qrCodeSvg
        );
        
        // For PDF, use base64 encoded SVG
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
            
        return [
            'user' => $user,
            'qrCode' => $qrCodeForWeb,  // For web view (raw SVG with dimensions)
            'qrCodePdf' => $qrCodeBase64, // For PDF (base64 encoded SVG)
            'url' => $url
        ];
    }


    /**
     * Generate unique barcode code for the user
     */
    protected function generateBarcodeCode($user)
    {
        // Gunakan ID user dan timestamp untuk membuat kode unik
        $uniqueCode = substr(md5($user->id . '-' . $user->created_at), 0, 12);
        return strtoupper($uniqueCode);
    }
    
    /**
     * Show LKT page after scanning barcode
     */
    public function showLkt($vendorId, $code)
    {
        $vendor = Vendor::with('user')->find($vendorId);
        
        // If vendor not found, try to get user data
        if (!$vendor) {
            $user = User::find($vendorId);
            if ($user) {
                $vendor = (object)[
                    'id' => $user->id,
                    'nama_vendor' => $user->name,
                    'kode_vendor' => $user->kode_vendor ?? 'USER-' . $user->id,
                    'user' => $user
                ];
            }
        }
        
        if (!$vendor) {
            abort(404, 'Data vendor tidak ditemukan');
        }
        
        // Verify barcode code if user exists
        if ($vendor->user && $code !== $this->generateBarcodeCode($vendor->user)) {
            abort(404, 'Kode barcode tidak valid');
        }
        
        // Save vendor data in session for LKT page
        session(['current_vendor' => $vendor]);
        
        // Redirect to LKT page
        return redirect()->route('lkt.index');
    }
    
    /**
     * Menampilkan halaman LKT
     */
    public function lktIndex()
    {
        // Dapatkan data vendor dari session
        $vendor = session('current_vendor');
        
        if (!$vendor) {
            return redirect('/')->with('error', 'Akses tidak valid. Harus melalui scan barcode.');
        }
        
        return view('lkt.index', compact('vendor'));
    }
}
        