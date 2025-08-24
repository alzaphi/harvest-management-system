<?php

namespace App\Http\Controllers;

use App\Models\StatusSubBlock;
use App\Models\SubBlock;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StatusSubBlocksImport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StatusSubBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = StatusSubBlock::with('subBlock')
            ->join('sub_blocks', 'status_sub_blocks.kode_petak', '=', 'sub_blocks.kode_petak')
            ->orderBy('sub_blocks.kode_petak')
            ->select('status_sub_blocks.*');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereRaw('UPPER(status_sub_blocks.kode_petak) LIKE ?', ['%'.strtoupper($search).'%'])
                  ->orWhereRaw('UPPER(sub_blocks.blok) LIKE ?', ['%'.strtoupper($search).'%']);
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status_sub_blocks.status', $request->status);
        }

        if ($request->has('tahun') && $request->tahun !== '') {
            $query->whereYear('status_sub_blocks.tanggal_update', $request->tahun);
        }

        $statusSubBlocks = $query->paginate(13);
        $statusSubBlocks->appends($request->query());

        return view('backend.subblock.status.status', compact('statusSubBlocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all sub-blocks that don't have a status entry yet
        $subBlocks = SubBlock::whereNotIn('kode_petak', function($query) {
            $query->select('kode_petak')->from('status_sub_blocks');
        })->orderBy('kode_petak')->get();

        return view('backend.subblock.status.create', compact('subBlocks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle the checkbox value before validation
        $request->merge(['aktif' => $request->has('aktif')]);

        $validated = $request->validate([
            'kode_petak' => 'required|string|exists:sub_blocks,kode_petak',
            'tanggal_update' => 'required|date',
            'status' => 'required|string|max:100',
            'luas_status' => 'required|numeric|min:0',
            'aktif' => 'required|boolean',
        ]);

        $validated['tahun'] = date('Y', strtotime($validated['tanggal_update']));

        try {
            // Log the input data for debugging
            Log::info('Attempting to create StatusSubBlock with data:', $validated);

            $statusSubBlock = StatusSubBlock::create($validated);

            // Log successful creation
            Log::info('StatusSubBlock created successfully', ['id' => $statusSubBlock->id]);

            return redirect()->route('status-sub-blocks.index')
                ->with('success', 'Status Sub Block berhasil ditambahkan');

        } catch (\Exception $e) {
            // Log detailed error information
            Log::error('Error creating status sub block', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan Status Sub Block: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StatusSubBlock $statusSubBlock)
    {
        return view('backend.subblock.status.show', compact('statusSubBlock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StatusSubBlock $statusSubBlock)
    {
        $subBlocks = SubBlock::orderBy('kode_petak')->get();
        return view('backend.subblock.status.edit', compact('statusSubBlock', 'subBlocks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StatusSubBlock $statusSubBlock)
    {
        $validated = $request->validate([
            'kode_petak' => 'required|string|exists:sub_blocks,kode_petak',
            'tanggal_update' => 'required|date',
            'status' => 'required|string|max:100',
            'luas_status' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['tahun'] = date('Y', strtotime($validated['tanggal_update']));

        try {
            $statusSubBlock->update($validated);
            return redirect()->route('status-sub-blocks.index')
                ->with('success', 'Status Sub Block berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating status sub block: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui Status Sub Block');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, StatusSubBlock $statusSubBlock)
    {
        try {
            $statusSubBlock->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status Sub Block berhasil dihapus'
                ]);
            }

            return redirect()->route('status-sub-blocks.index')
                ->with('success', 'Status Sub Block berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Error deleting status sub block: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus Status Sub Block: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus Status Sub Block');
        }
    }

    /**
     * Download Excel template for importing status sub blocks
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'kode_petak', 'status', 'luas_status', 'tanggal_update', 'aktif'
        ];
        
        // Add header row
        $sheet->fromArray($headers, null, 'A1');
        
        // Add sample data rows
        $sampleData = [
            ['LB01A', 'Planned Cutting', 1.5, date('Y-m-d'), 1],
            ['LB01B', 'Already Cut Down', 2.0, date('Y-m-d'), 0],
            ['LB02A', 'Planned Cutting', 1.0, date('Y-m-d'), 1],
            ['LB02B', 'Already Cut Down', 1.8, date('Y-m-d'), 0],
        ];
        
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Style the header row
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        
        // Auto size columns for better readability
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create a temporary file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'template_import_status_sub_block.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);
        
        // Download the file
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Import status sub blocks from Excel file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Store the file temporarily
        $file = $request->file('file');

        try {
            $import = new StatusSubBlocksImport;

            // Use queue import for better performance with large files
            Excel::import($import, $file);


            // Get the number of processed rows (excluding header)
            $processed = $import->getProcessedCount();

            return redirect()->route('status-sub-blocks.index')
                ->with('success', "Berhasil mengimport $processed data Status Sub Block");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ' . $failure->row() . ": " .
                                 implode(', ', $failure->errors());
            }


            return back()
                ->with('error', 'Gagal mengimport data: ' .
                    implode(' ', $errorMessages))
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Error importing status sub blocks: ' . $e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
}
