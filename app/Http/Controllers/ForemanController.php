<?php

namespace App\Http\Controllers;

use App\Models\Foreman;
use Illuminate\Http\Request;
use App\Exports\ForemanExport;

class ForemanController extends Controller
{
    public function index(Request $request)
    {
        $query = Foreman::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_mandor', 'like', "%{$search}%")
                  ->orWhere('nama_mandor', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $foremen = $query->orderBy('kode_mandor', 'asc')->paginate(15)->withQueryString();
        
        // Set breadcrumb for foreman index page
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => url('/')],
            ['title' => 'List Mandor']
        ];
        
        return view('foreman.index', compact('foremen', 'breadcrumb'));
    }

    public function create()
    {
        // Get the latest kode_mandor
        $latest = Foreman::where('kode_mandor', 'like', 'MA%')
            ->orderBy('kode_mandor', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            // Extract the number part and increment
            $nextNumber = (int) substr($latest->kode_mandor, 2) + 1;
        }
        
        // Format the next kode_mandor
        $nextKodeMandor = 'MA' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Set breadcrumb for create foreman page
        $breadcrumb = [
            ['title' => 'List Mandor', 'url' => route('foreman.index')],
            ['title' => 'Mandor Baru']
        ];
        
        return view('foreman.create', compact('breadcrumb', 'nextKodeMandor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mandor' => 'required|string|max:100',
            'email' => 'required|email|unique:foreman,email|max:100',
            'no_hp' => 'required|string|regex:/^[0-9]{10,13}$/|max:13',
            'status' => 'required|in:Aktif,Nonaktif',
        ], [
            'no_hp.regex' => 'Nomor HP harus berisi 10-13 digit angka',
            'no_hp.max' => 'Nomor HP maksimal 13 digit',
        ]);

        Foreman::create($validated);

        return redirect()->route('foreman.index')
                         ->with('success', 'Data mandor berhasil ditambahkan!');
    }

    public function edit(Foreman $foreman)
    {
        // Set breadcrumb for edit foreman page
        $breadcrumb = [
            ['title' => 'List Mandor', 'url' => route('foreman.index')],
            ['title' => 'Edit Mandor']
        ];
        
        return view('foreman.edit', compact('foreman', 'breadcrumb'));
    }

    public function update(Request $request, Foreman $foreman)
    {
        $validated = $request->validate([
            'nama_mandor' => 'required|string|max:100',
            'email' => 'required|email|unique:foreman,email,' . $foreman->id . '|max:100',
            'no_hp' => 'required|string|regex:/^[0-9]{10,13}$/|max:13',
            'status' => 'required|in:Aktif,Nonaktif',
        ], [
            'no_hp.regex' => 'Nomor HP harus berisi 10-13 digit angka',
            'no_hp.max' => 'Nomor HP maksimal 13 digit',
        ]);

        $foreman->update($validated);

        return redirect()->route('foreman.index')
                         ->with('success', 'Data mandor berhasil diperbarui!');
    }

    public function destroy(Foreman $foreman)
    {
        try {
            $foreman->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data mandor berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('foreman.index')
                             ->with('success', 'Data mandor berhasil dihapus!');
                             
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

    public function exportExcel(Request $request)
    {
        $search = $request->input('search');
        
        // Get the data
        $export = new ForemanExport($search);
        $data = $export->collection();
        $headings = $export->headings();
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->fromArray([$headings], null, 'A1');
        
        // Set data
        $row = 2;
        foreach ($data as $item) {
            $sheet->fromArray($export->map($item), null, 'A' . $row);
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'Z') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create a writer and save the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'data_mandor_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);
        
        // Return the file as a download
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
