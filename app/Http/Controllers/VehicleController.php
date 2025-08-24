<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\JenisUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleExport;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $vehicles = Vehicle::with(['vendor', 'jenisUnit'])
            ->when($search, function($query) use ($search) {
                $query->where('kode_lambung', 'like', "%{$search}%")
                      ->orWhere('plat_nomor', 'like', "%{$search}%")
                      ->orWhereHas('vendor', function($q) use ($search) {
                          $q->where('nama_vendor', 'like', "%{$search}%");
                      });
            })
            ->orderBy('kode_vendor')
            ->orderBy('kode_lambung')
            ->paginate(15)
            ->withQueryString();
        
        // Set breadcrumb for vehicle list page
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => url('/')],
            ['title' => 'List Kendaraan Vendor']
        ];
            
        return view('vehicles.vehicle-list', compact('vehicles', 'breadcrumb'));
    }

    public function vendorVehicleList(Request $request)
    {
        $search = $request->input('search');
        
        $vehicles = Vehicle::with(['vendor', 'jenisUnit'])
            ->when($search, function($query) use ($search) {
                $query->where('kode_lambung', 'like', "%{$search}%")
                      ->orWhere('plat_nomor', 'like', "%{$search}%")
                      ->orWhereHas('vendor', function($q) use ($search) {
                          $q->where('nama_vendor', 'like', "%{$search}%");
                      });
            })
            ->orderBy('kode_vendor')
            ->orderBy('kode_lambung')
            ->paginate(15)
            ->withQueryString();
        
        // Set breadcrumb for vendor vehicle list page
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => url('/')],
            ['title' => 'List Kendaraan Vendor']
        ];
            
        return view('vehicles.vehicle-list', compact('vehicles', 'breadcrumb'));
    }

    public function exportExcel(Request $request)
    {
        $search = $request->input('search');
        return Excel::download(new \App\Exports\VehicleExport($search), 'kendaraan-vendor-' . date('Ymd_His') . '.xlsx');
    }

    public function create()
    {
        // Get only vendors with kode_vendor starting with 'VA' (Vendor Angkut)
        $vendors = Vendor::where('kode_vendor', 'LIKE', 'VA%')
            ->select('kode_vendor', 'nama_vendor')
            ->orderBy('kode_vendor')
            ->get();
            
        if ($vendors->isEmpty()) {
            $vendors = collect([
                ['kode_vendor' => 'VA00001', 'nama_vendor' => 'Vendor Angkut 1'],
                ['kode_vendor' => 'VA00002', 'nama_vendor' => 'Vendor Angkut 2'],
                ['kode_vendor' => 'VA00003', 'nama_vendor' => 'Vendor Angkut 3']
            ]);
        }

        // Get all jenis unit
        $jenisUnits = JenisUnit::orderBy('nama_unit')
            ->pluck('nama_unit', 'id');

        // Generate the next kode_lambung
        $nextKodeLambung = \App\Models\Vehicle::generateKodeLambung();

        // Set breadcrumb for create vehicle page
        $breadcrumb = [
            ['title' => 'List Kendaraan Vendor', 'url' => route('vehicles.index')],
            ['title' => 'Kendaraan Baru Vendor']
        ];
        
        return view('vehicles.vehicle-create', compact('vendors', 'nextKodeLambung', 'breadcrumb', 'jenisUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_vendor' => 'required|exists:vendor_angkut,kode_vendor',
            'nama_vendor' => 'required|string|max:255',
            'kode_lambung' => 'required|string|max:50|unique:vehicle,kode_lambung',
            'plat_nomor' => 'required|string|max:20|unique:vehicle,plat_nomor',
            'jenis_unit_id' => 'required|exists:jenis_unit,id',
        ], [
            'plat_nomor.unique' => 'Nomor polisi sudah digunakan oleh kendaraan lain',
            'kode_lambung.unique' => 'Kode lambung sudah digunakan',
        ]);

        try {
            Vehicle::create($validated);
            return redirect()->route('vehicles.index')
                ->with('success', 'Data kendaraan berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $vehicle = Vehicle::with('jenisUnit')->findOrFail($id);
        
        $vendors = Vendor::where('kode_vendor', 'LIKE', 'VA%')
            ->select('kode_vendor', 'nama_vendor')
            ->orderBy('kode_vendor')
            ->get();
            
        if ($vendors->isEmpty()) {
            $vendors = collect([
                ['kode_vendor' => 'VA00001', 'nama_vendor' => 'Vendor Angkut 1'],
                ['kode_vendor' => 'VA00002', 'nama_vendor' => 'Vendor Angkut 2'],
                ['kode_vendor' => 'VA00003', 'nama_vendor' => 'Vendor Angkut 3']
            ]);
        }

        // Get all jenis unit
        $jenisUnits = JenisUnit::orderBy('nama_unit')
            ->pluck('nama_unit', 'id');

        $breadcrumb = [
            ['title' => 'List Kendaraan Vendor', 'url' => route('vehicles.index')],
            ['title' => 'Edit Kendaraan']
        ];
        
        return view('vehicles.vehicle-edit', compact('vehicle', 'vendors', 'breadcrumb', 'jenisUnits'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $validated = $request->validate([
            'kode_vendor' => 'required|exists:vendor_angkut,kode_vendor',
            'nama_vendor' => 'required|string|max:255',
            'kode_lambung' => 'required|string|max:50|unique:vehicle,kode_lambung,' . $id,
            'plat_nomor' => 'required|string|max:20|unique:vehicle,plat_nomor,' . $id,
            'jenis_unit_id' => 'required|exists:jenis_unit,id',
        ], [
            'plat_nomor.unique' => 'Nomor polisi sudah digunakan oleh kendaraan lain',
            'kode_lambung.unique' => 'Kode lambung sudah digunakan',
        ]);

        try {
            $vehicle->update($validated);
            return redirect()->route('vehicles.index')
                ->with('success', 'Data kendaraan berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data kendaraan berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('vendor.vehicle.list')
                ->with('success', 'Data kendaraan berhasil dihapus!');
                
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ]);
        }
    }
}
