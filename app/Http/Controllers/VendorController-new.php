<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VendorExport;

class VendorController extends Controller
{
    // ... [previous methods remain the same until the store method]

    public function store(Request $request)
    {
        $rules = [
            'nama_vendor' => 'required|string|max:255',
            'no_hp' => 'required|string|max:15',
            'jenis_vendor' => 'required|string|in:angkut,tebang,both',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:100',
        ];

        // Rest of the store method remains the same
        // ...
    }


    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $jenisVendor = $request->input('jenis_vendor');

        $rules = [
            'nama_vendor' => 'required|string|max:255',
            'no_hp' => 'required|string|max:15',
            'jenis_vendor' => 'required|string|in:angkut,tebang,both',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:100',
        ];

        // Rest of the update method remains the same
        // ...
    }

    // ... [rest of the file remains the same]
}
