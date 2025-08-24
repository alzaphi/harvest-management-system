<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VendorExport;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-vendors', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-vendor', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-vendor', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-vendor', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = Vendor::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_vendor', 'like', "%$search%")
                  ->orWhere('kode_vendor', 'like', "%$search%")
                  ->orWhere('no_hp', 'like', "%$search%")
                  ->orWhere('jenis_vendor', 'like', "%$search%");
            });
        }

        // Handle status filter - only apply if status is 'aktif' or 'nonaktif'
        if ($request->has('status') && in_array($request->status, ['aktif', 'nonaktif'])) {
            $query->where('status', $request->status);
        }

        // Handle vendor type filter
        $jenisVendor = $request->input('jenis_vendor', '');

        if ($jenisVendor === 'angkut') {
            $query->where('kode_vendor', 'LIKE', 'VA%');
        } elseif ($jenisVendor === 'tebang') {
            $query->where('kode_vendor', 'LIKE', 'VT%');
        } elseif ($jenisVendor === 'both') {
            // Get all vendor names that have both VA and VT types
            $vendorNames = Vendor::select('nama_vendor')
                ->where(function($q) {
                    $q->where('kode_vendor', 'LIKE', 'VA%')
                      ->orWhere('kode_vendor', 'LIKE', 'VT%');
                })
                ->groupBy('nama_vendor')
                ->havingRaw('COUNT(DISTINCT LEFT(kode_vendor, 2)) = 2')
                ->pluck('nama_vendor');

            $query->whereIn('nama_vendor', $vendorNames);
        }

        // Get unique vendors with jumlah_tenaga_kerja > 0
        $tenagaKerjaVendors = (clone $query)
            ->where('jumlah_tenaga_kerja', '>', 0)
            ->whereIn('id', function($q) use ($request) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('vendor_angkut')
                  ->where('jumlah_tenaga_kerja', '>', 0);
                
                // Only apply status filter if a specific status is selected
                if ($request->has('status') && $request->status !== '' && in_array($request->status, ['aktif', 'nonaktif'])) {
                    $q->where('status', $request->status);
                }
                
                $q->groupBy('nama_vendor');
            })
            ->orderBy('nama_vendor')
            ->paginate(15, ['*'], 'tenaga_kerja_page')
            ->withQueryString();

        // Paginate for normal view
        if ($jenisVendor === 'both') {
            $vendors = $query->orderBy('nama_vendor')
                          ->orderBy('kode_vendor')
                          ->paginate(15);
        } else {
            $vendors = $query->orderBy('kode_vendor')
                          ->paginate(15);
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('vendor.partials.vendor_table', compact('vendors'))->render(),
                'pagination' => (string) $vendors->links()
            ]);
        }

        // Check if we're on the tenaga kerja page
        $isTenagaKerjaPage = $request->has('tenaga_kerja_page');

        return view('vendor.index', [
            'vendors' => $vendors,
            'tenagaKerjaVendors' => $tenagaKerjaVendors,
            'isTenagaKerjaPage' => $isTenagaKerjaPage
        ]);
    }

    public function create()
    {
        // Generate unique kode for angkut (format: VA + 5 digits)
        $newKode = $this->generateUniqueVendorCode('VA');

        // Generate unique kode for tebang (format: VT + 5 digits)
        $newKodeTebang = $this->generateUniqueVendorCode('VT');

        return view('vendor.create', compact('newKode', 'newKodeTebang'));
    }

    /**
     * Generate a unique vendor code
     *
     * @param string $prefix Code prefix (VA or VT)
     * @return string
     */
    private function generateUniqueVendorCode($prefix = 'VA')
    {
        $lastVendor = Vendor::where('kode_vendor', 'LIKE', $prefix . '%')
            ->orderBy('kode_vendor', 'desc')
            ->first();

        if ($lastVendor) {
            $lastNumber = (int) substr($lastVendor->kode_vendor, 2);
            $newNumber = $lastNumber + 1;

            // Keep incrementing until we find an unused code
            while (Vendor::where('kode_vendor', $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT))->exists()) {
                $newNumber++;

                // Safety check to prevent infinite loop
                if ($newNumber > 99999) {
                    throw new \Exception('Tidak dapat menghasilkan kode vendor unik');
                }
            }

            return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
        }

        // If no vendors exist yet, start from 00001
        return $prefix . '00001';
    }

    public function store(Request $request)
    {
        $jenisVendor = $request->input('jenis_vendor');

        $rules = [
            'nama_vendor' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($jenisVendor) {
                    $query = Vendor::where('nama_vendor', $value);

                    if ($jenisVendor === 'both') {
                        $query->where(function($q) {
                            $q->where('jenis_vendor', 'like', '%Angkut%')
                              ->orWhere('jenis_vendor', 'like', '%Tebang%')
                              ->orWhere('jenis_vendor', 'like', '%&%');
                        });
                    } else {
                        $query->where('jenis_vendor', 'like', '%' . ucfirst($jenisVendor) . '%');
                    }

                    if ($query->exists()) {
                        $vendorType = $jenisVendor === 'both' ? 'angkut/tebang' : $jenisVendor;
                        $fail("Vendor ini sudah terdaftar sebagai vendor {$vendorType}.");
                    }
                }
            ],
            'no_hp' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'min:10',
                'max:12',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[0-9]+$/', $value)) {
                        $fail('Nomor HP hanya boleh berisi angka.');
                    }
                    if (strlen($value) < 10 || strlen($value) > 12) {
                        $fail('Nomor HP harus terdiri dari 10-12 digit angka.');
                    }
                    if (!str_starts_with($value, '08')) {
                        $fail('Nomor HP harus diawali dengan 08.');
                    }
                }
            ],
            'jenis_vendor' => 'required|string|in:angkut,tebang,both',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'jumlah_tenaga_kerja' => 'required|integer|min:1',
            'nomor_rekening' => 'required|integer|min:1',
            'nama_bank' => 'required|string|min:3|max:100',
        ];

        // Add kode_vendor_angkut validation
        $rules['kode_vendor_angkut'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('vendor_angkut', 'kode_vendor')
        ];

        // If jenis_vendor is 'both', also validate kode_vendor_tebang
        if ($request->input('jenis_vendor') === 'both') {
            $rules['kode_vendor_tebang'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('vendor_angkut', 'kode_vendor')
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            $jenisVendor = $request->input('jenis_vendor');
            $kodeAngkut = $request->input('kode_vendor_angkut');
            $kodeTebang = $request->input('kode_vendor_tebang');

            $vendorData = [
                'nama_vendor' => $request->input('nama_vendor'),
                'no_hp' => $request->input('no_hp'),
                'status' => $request->input('status'),
                'jumlah_tenaga_kerja' => $request->input('jumlah_tenaga_kerja'),
                'nomor_rekening' => $request->input('nomor_rekening'),
                'nama_bank' => $request->input('nama_bank'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Create vendor record(s) based on the selected type
            if ($jenisVendor === 'both') {
                // Create Vendor Angkut record
                Vendor::create(array_merge($vendorData, [
                    'kode_vendor' => $kodeAngkut,
                    'jenis_vendor' => 'Vendor Angkut',
                ]));

                // Create Vendor Tebang record
                Vendor::create(array_merge($vendorData, [
                    'kode_vendor' => $kodeTebang,
                    'jenis_vendor' => 'Vendor Tebang',
                ]));
            } else {
                // Create single vendor record
                if ($jenisVendor === 'tebang') {
                    // Gunakan kode tebang untuk vendor tebang
                    Vendor::create(array_merge($vendorData, [
                        'kode_vendor' => $kodeTebang,
                        'jenis_vendor' => 'Vendor Tebang',
                    ]));
                } else {
                    // Default ke vendor angkut
                    Vendor::create(array_merge($vendorData, [
                        'kode_vendor' => $kodeAngkut,
                        'jenis_vendor' => 'Vendor Angkut',
                    ]));
                }
            }

            DB::commit();
            return redirect()->route('vendor.index')->with('success', 'Vendor berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating vendor: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Gagal menambahkan vendor: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);

        // Generate unique codes for both types
        $newKodeAngkut = $this->generateUniqueVendorCode('VA');
        $newKodeTebang = $this->generateUniqueVendorCode('VT');

        // Map the stored jenis_vendor to the form value
        $jenisVendorMap = [
            'Vendor Angkut' => 'angkut',
            'Vendor Tebang' => 'tebang',
            'Vendor Angkut & Tebang' => 'both'
        ];

        $vendor->jenis_vendor = $jenisVendorMap[$vendor->jenis_vendor] ?? 'angkut';

        // Initialize code fields with current values
        $vendor->kode_vendor_angkut = '';
        $vendor->kode_vendor_tebang = '';

        // Set the current vendor code based on its type
        if (str_starts_with($vendor->kode_vendor, 'VA')) {
            $vendor->kode_vendor_angkut = $vendor->kode_vendor;
            $vendor->kode_vendor_tebang = $newKodeTebang;
        } elseif (str_starts_with($vendor->kode_vendor, 'VT')) {
            $vendor->kode_vendor_tebang = $vendor->kode_vendor;
            $vendor->kode_vendor_angkut = $newKodeAngkut;
        }

        // If this is part of a 'both' type, find the other vendor code
        if ($vendor->jenis_vendor === 'both') {
            $otherVendor = Vendor::where('nama_vendor', $vendor->nama_vendor)
                ->where('id', '!=', $vendor->id)
                ->first();

            if ($otherVendor) {
                if (str_starts_with($otherVendor->kode_vendor, 'VA')) {
                    $vendor->kode_vendor_angkut = $otherVendor->kode_vendor;
                    // Generate a new unique code for tebang if needed
                    if (Vendor::where('kode_vendor', $vendor->kode_vendor_tebang)->exists()) {
                        $vendor->kode_vendor_tebang = $this->generateUniqueVendorCode('VT');
                    }
                } else {
                    $vendor->kode_vendor_tebang = $otherVendor->kode_vendor;
                    // Generate a new unique code for angkut if needed
                    if (Vendor::where('kode_vendor', $vendor->kode_vendor_angkut)->exists()) {
                        $vendor->kode_vendor_angkut = $this->generateUniqueVendorCode('VA');
                    }
                }
            }
        }

        return view('vendor.edit', [
            'vendor' => $vendor,
            'newKodeAngkut' => $newKodeAngkut,
            'newKodeTebang' => $newKodeTebang
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $jenisVendor = $request->input('jenis_vendor');

        $rules = [
            'nama_vendor' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($vendor, $jenisVendor) {
                    $query = Vendor::where('nama_vendor', $value)
                        ->where('id', '!=', $vendor->id);

                    if ($jenisVendor === 'both') {
                        $query->where(function($q) {
                            $q->where('jenis_vendor', 'like', '%Angkut%')
                              ->orWhere('jenis_vendor', 'like', '%Tebang%')
                              ->orWhere('jenis_vendor', 'like', '%&%');
                        });
                    } else {
                        $query->where('jenis_vendor', 'like', '%' . ucfirst($jenisVendor) . '%');
                    }

                    if ($query->exists()) {
                        $vendorType = $jenisVendor === 'both' ? 'angkut/tebang' : $jenisVendor;
                        $fail("Vendor ini sudah terdaftar sebagai vendor {$vendorType}.");
                    }
                }
            ],
            'no_hp' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9]+$/',
                'min:10',
                'max:12',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[0-9]+$/', $value)) {
                        $fail('Nomor HP hanya boleh berisi angka.');
                    }
                    if (strlen($value) < 10 || strlen($value) > 12) {
                        $fail('Nomor HP harus terdiri dari 10-12 digit angka.');
                    }
                    if (!str_starts_with($value, '08')) {
                        $fail('Nomor HP harus diawali dengan 08.');
                    }
                }
            ],
            'jenis_vendor' => 'required|string|in:angkut,tebang,both',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'jumlah_tenaga_kerja' => 'required|integer|min:1',
            'nomor_rekening' => 'required|integer|min:1',
            'nama_bank' => 'required|string|min:3|max:100',
        ];

        // Validasi kode vendor angkut
        $kodeAngkut = $request->input('kode_vendor_angkut');
        $kodeTebang = $request->input('kode_vendor_tebang');

        // Validasi kode vendor angkut
        $rules['kode_vendor_angkut'] = [
            'required',
            'string',
            'max:10',
            function ($attribute, $value, $fail) use ($id) {
                $exists = DB::table('vendor_angkut')
                    ->where('kode_vendor', $value)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    $fail('Kode vendor angkut sudah digunakan.');
                }
            },
        ];

        // Jika jenis_vendor adalah 'both', validasi kode vendor tebang
        if ($jenisVendor === 'both') {
            $rules['kode_vendor_tebang'] = [
                'required',
                'string',
                'max:10',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = DB::table('vendor_angkut')
                        ->where('kode_vendor', $value)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($exists) {
                        $fail('Kode vendor tebang sudah digunakan.');
                    }
                },
            ];
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Get the worker count from the request
            $jumlahTenagaKerja = $request->input('jumlah_tenaga_kerja', 0);

            // First, update jumlah_tenaga_kerja for all vendor records with the same name
            if ($request->has('jumlah_tenaga_kerja')) {
                DB::table('vendor_angkut')
                    ->where('nama_vendor', $request->input('nama_vendor'))
                    ->update(['jumlah_tenaga_kerja' => $jumlahTenagaKerja]);
            }

            $vendorData = [
                'nama_vendor' => $request->input('nama_vendor'),
                'no_hp' => $request->input('no_hp'),
                'status' => $request->input('status'),
                'nomor_rekening' => $request->input('nomor_rekening'),
                'nama_bank' => $request->input('nama_bank'),
                'jenis_vendor' => $jenisVendor === 'both' ? 'Vendor Angkut & Tebang' : 'Vendor ' . ucfirst($jenisVendor)
                // jumlah_tenaga_kerja is handled separately above
            ];

            if ($jenisVendor === 'both') {
                // Jika mengubah dari vendor tebang ke keduanya
                if (str_starts_with($vendor->kode_vendor, 'VT')) {
                    // Cek dulu apakah kode vendor angkut sudah digunakan
                    $existingAngkut = Vendor::where('kode_vendor', $kodeAngkut)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($existingAngkut) {
                        // Jika kode sudah digunakan, generate kode baru yang unik
                        $kodeAngkut = $this->generateUniqueVendorCode('VA');

                        // Update kode vendor angkut di form
                        return back()
                            ->withInput()
                            ->with('kode_vendor_angkut', $kodeAngkut)
                            ->with('warning', 'Kode vendor angkut sudah digunakan. Kode baru telah digenerate.');
                    }

                    // Update vendor tebang yang ada
                    $vendor->update([
                        'kode_vendor' => $kodeTebang,
                        'jenis_vendor' => 'Vendor Tebang'
                    ] + $vendorData);

                    // Buat vendor angkut baru
                    Vendor::create([
                        'kode_vendor' => $kodeAngkut,
                        'jenis_vendor' => 'Vendor Angkut'
                    ] + $vendorData);
                }
                // Jika mengubah dari vendor angkut ke keduanya
                elseif (str_starts_with($vendor->kode_vendor, 'VA')) {
                    // Update vendor angkut yang ada
                    $vendor->update([
                        'kode_vendor' => $kodeAngkut,
                        'jenis_vendor' => 'Vendor Angkut'
                    ] + $vendorData);

                    // Cek apakah vendor tebang sudah ada
                    $existingTebang = DB::table('vendor_angkut')
                        ->where('kode_vendor', $kodeTebang)
                        ->where('id', '!=', $id)
                        ->first();

                    // Buat vendor tebang baru jika belum ada
                    if (!$existingTebang) {
                        DB::table('vendor_angkut')->insert([
                            'kode_vendor' => $kodeTebang,
                            'jenis_vendor' => 'Vendor Tebang'
                        ] + $vendorData);
                    }
                }
            } else {
                // Update single vendor record
                $kodeVendor = $jenisVendor === 'tebang' ? $kodeTebang : $kodeAngkut;
                $jenisVendorDisplay = $jenisVendor === 'tebang' ? 'Vendor Tebang' : 'Vendor Angkut';

                // Update vendor yang ada
                $vendor->update([
                    'kode_vendor' => $kodeVendor,
                    'jenis_vendor' => $jenisVendorDisplay,
                    'nama_vendor' => $vendorData['nama_vendor'],
                    'no_hp' => $vendorData['no_hp'],
                    'status' => $vendorData['status'],
                    'nomor_rekening' => $vendorData['nomor_rekening'],
                    'nama_bank' => $vendorData['nama_bank']
                    // jumlah_tenaga_kerja is already updated for all records with the same name
                ]);

                // Jika sebelumnya adalah tipe 'both', hapus record yang tidak diperlukan
                if (str_contains($vendor->jenis_vendor, '&')) {
                    $otherKode = $jenisVendor === 'tebang' ? $kodeAngkut : $kodeTebang;

                    // Update jumlah_tenaga_kerja for all vendor records with the same name
                    DB::table('vendor_angkut')
                        ->where('nama_vendor', $vendor->nama_vendor)
                        ->update(['jumlah_tenaga_kerja' => $vendorData['jumlah_tenaga_kerja']]);

                    // Hapus record lain dengan kode yang berbeda jika bukan tipe 'both'
                    if ($jenisVendor !== 'both') {
                        DB::table('vendor_angkut')
                            ->where('nama_vendor', $vendor->nama_vendor)
                            ->where('kode_vendor', '!=', $kodeVendor)
                            ->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui vendor: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);

        try {
            // Check if vendor has associated vehicles
            if ($vendor->vehicles()->exists()) {
                $message = 'Tidak dapat menghapus vendor karena memiliki kendaraan yang terkait.';
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }
                
                return back()->with('error', $message);
            }

            DB::beginTransaction();
            $vendor->delete();
            DB::commit();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vendor berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Gagal menghapus vendor: ' . $e->getMessage();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    public function exportExcel(Request $request)
    {
        $isWorkerList = $request->has('view') && $request->input('view') === 'tenaga-kerja';
        $search = $request->input('search');
        $jenis_vendor = $isWorkerList ? null : $request->input('jenis_vendor');

        $fileName = $isWorkerList
            ? 'daftar_tenaga_kerja_' . date('Ymd_His') . '.xlsx'
            : 'daftar_vendor_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new VendorExport($search, $jenis_vendor, $isWorkerList),
            $fileName
        );
    }
}
