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

class LKTController extends Controller
{
    // ... [keep all other methods unchanged] ...

    /**
     * Save signature to storage
     *
     * @param string $signature Base64 encoded signature image
     * @param string $path Storage path (default: 'signatures')
     * @return string Path to the saved signature
     * @throws \Exception
     */
    private function saveSignature($signature, $path = 'signatures')
    {
        try {
            $image = str_replace('data:image/png;base64,', '', $signature);
            $image = str_replace(' ', '+', $image);
            
            // Generate a unique filename
            $imageName = 'signature_' . Str::random(10) . '.png';
            
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

    // ... [keep all other methods unchanged] ...
}
