<?php

namespace App\Http\Controllers;

use App\Models\HasilTebang;
use App\Models\LKT;
use App\Models\VendorAngkut;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BappTebang;

class HasilTebangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $jenis = $request->jenis ?? 'tebang';

        $vendors = VendorAngkut::where('jenis_vendor', $jenis === 'angkut' ? 'Vendor Angkut' : 'Vendor Tebang')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_vendor', 'like', "%$search%")
                      ->orWhere('kode_vendor', 'like', "%$search%")
                      ->orWhere('alamat', 'like', "%$search%")
                      ->orWhere('kontak', 'like', "%$search%");
            })
            ->get();

        return view('hasiltebang.index', compact('vendors', 'jenis'));
    }

    public function create()
    {
        $lastId = HasilTebang::max('id') ?? 0;
        $kodeHasilTebang = 'HT-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        $usedLkts = HasilTebang::pluck('kode_lkt')->toArray();
        $lkts = LKT::where('status', 'disetujui')
            ->whereNotIn('kode_lkt', $usedLkts)
            ->with(['vendorTebang', 'vendorAngkut', 'driver', 'petak'])
            ->get()
            ->map(function ($lkt) {
                return [
                    'kode_lkt' => $lkt->kode_lkt ?? '',
                    'kode_spt' => $lkt->kode_spt ?? '',
                    'kode_petak' => $lkt->kode_petak ?? '',
                    'divisi' => $lkt->petak->divisi ?? 'N/A',
                    'vendor_tebang' => $lkt->vendorTebang ? $lkt->vendorTebang->kode_vendor : null,
                    'vendor_angkut' => $lkt->vendorAngkut ? $lkt->vendorAngkut->kode_vendor : null,
                    'zonasi' => $lkt->petak->zona ?? 'N/A',
                    'jenis_tebangan' => $lkt->jenis_tebangan ?? 'N/A',
                    'kode_lambung' => $lkt->driver ? $lkt->driver->kode_lambung : ''
                ];
            });

        return view('hasiltebang.create', [
            'kodeHasilTebang' => $kodeHasilTebang,
            'lkts' => $lkts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_hasil_tebang' => 'required|string|max:50|unique:hasil_tebang',
            'tanggal_timbang' => 'required|date',
            'kode_lkt' => 'required|string|exists:lkt,kode_lkt',
            'kode_spt' => 'required|string',
            'kode_petak' => 'required|string',
            'vendor_tebang' => 'required|string',
            'vendor_angkut' => 'required|string',
            'jenis_tebang' => 'required|string',
            'bruto' => 'required|numeric|min:0',
            'tanggal_bruto' => 'required|date',
            'tarra' => 'required|numeric|min:0',
            'tanggal_tarra' => 'required|date',
            'netto1' => 'required|numeric|min:0',
            'sortase' => 'required|numeric|min:0',
            'netto2' => 'required|numeric|min:0',
            'divisi' => 'required|string',
            'zonasi' => 'required|string',
            'kode_lambung' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            $hasilTebang = new HasilTebang();
            $hasilTebang->fill($validated);
            $hasilTebang->status = 'Not Generated';
            $hasilTebang->status_angkut = 'Not Generated';
            $hasilTebang->save();
            DB::commit();

            return redirect()->route('hasil-tebang.index')
                ->with('success', 'Data hasil tebang berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($kode_vendor, Request $request)
    {
        $jenis = $request->query('jenis', 'tebang');
        $vendor = Vendor::where('kode_vendor', $kode_vendor)->firstOrFail();

        $query = HasilTebang::query();

        if ($jenis === 'angkut') {
            $query->where('vendor_angkut', $kode_vendor);
            $view = 'hasiltebang.showangkut';
        } else {
            $query->where('vendor_tebang', $kode_vendor);
            $view = 'hasiltebang.show';
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_hasil_tebang', 'like', "%$search%")
                    ->orWhere('kode_lkt', 'like', "%$search%")
                    ->orWhere('kode_spt', 'like', "%$search%");
            });
        }

        if ($request->has('tanggal') && !empty($request->tanggal)) {
            $query->whereDate('tanggal_timbang', $request->tanggal);
        }

        $hasilTebangs = $query->with(['vendorTebang', 'vendorAngkut', 'driver'])
            ->orderBy('tanggal_timbang', 'desc')
            ->paginate(10);

        return view($view, compact('vendor', 'hasilTebangs', 'jenis'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bruto' => 'required|numeric|min:0',
            'tarra' => 'required|numeric|min:0',
            'netto1' => 'required|numeric|min:0',
            'sortase' => 'required|numeric|min:0',
            'netto2' => 'required|numeric|min:0',
            'tanggal_bruto' => 'required|date',
            'tanggal_tarra' => 'required|date|after_or_equal:tanggal_bruto',
        ]);

        $hasilTebang = HasilTebang::findOrFail($id);
        $data = $request->only([
            'bruto', 'tarra', 'netto1', 'sortase', 'netto2',
            'tanggal_bruto', 'tanggal_tarra'
        ]);

        $data['tanggal_bruto'] = date('Y-m-d H:i:s', strtotime($request->tanggal_bruto));
        $data['tanggal_tarra'] = date('Y-m-d H:i:s', strtotime($request->tanggal_tarra));

        $hasilTebang->update($data);

        return redirect()
            ->route('hasil-tebang.index')
            ->with('success', 'Data hasil tebang berhasil diperbarui');
    }

    public function deleteSelection()
    {
        $allHasilTebang = HasilTebang::all();
        return view('hasiltebang.delete-selection', compact('allHasilTebang'));
    }

    public function editSelection()
    {
        $allHasilTebang = HasilTebang::orderBy('created_at', 'desc')->get();
        return view('hasiltebang.edit-selection', compact('allHasilTebang'));
    }

    public function deleteConfirm($id)
    {
        $hasil = HasilTebang::findOrFail($id);
        $vendorTebang = Vendor::where('kode_vendor', $hasil->vendor_tebang)->first();
        $vendorAngkut = Vendor::where('kode_vendor', $hasil->vendor_angkut)->first();

        return view('hasiltebang.delete-confirm', compact('hasil', 'vendorTebang', 'vendorAngkut'));
    }

    public function destroy($id)
    {
        $hasil = HasilTebang::findOrFail($id);
        $hasil->delete();

        return redirect()->route('hasil-tebang.index')
            ->with('success', 'Data hasil tebang berhasil dihapus.');
    }

    // public function generateSelection($kode_vendor)
    // {
    //     $vendor = VendorAngkut::where('kode_vendor', $kode_vendor)->firstOrFail();
    //     $hasilTebangs = HasilTebang::where('vendor_tebang', $kode_vendor)->get();

    //     return view('hasiltebang.generate-selection', compact('vendor', 'hasilTebangs'));
    // }

    // public function showGenerateSelection($vendorKode)
    // {
    //     $vendor = Vendor::where('kode_vendor', $vendorKode)->firstOrFail();
    //     $hasilTebangs = HasilTebang::where('vendor_tebang', $vendorKode)
    //         ->where('status', 'not_generated')
    //         ->get();

    //     return view('hasil-tebang.generate-selection', compact('vendor', 'hasilTebangs'));
    // }

    // public function showGenerateConfirm(Request $request)
    // {
    //     $request->validate([
    //         'hasil_tebang_ids' => 'required|array|min:1',
    //         'vendor_kode' => 'required'
    //     ]);

    //     $vendor = Vendor::where('kode_vendor', $request->vendor_kode)->firstOrFail();
    //     $hasilTebangs = HasilTebang::whereIn('id', $request->hasil_tebang_ids)->get();

    //     $kodeBAPP = 'BAPP-' . now()->format('Ymd-His');

    //     $totalPendapatan = $hasilTebangs->sum(function ($item) {
    //         return ($item->netto2 * 54000) + ($item->netto2 * 15000) + ($item->netto2 * 54000) + ($item->netto2 * 9000);
    //     });

    //     return view('hasil-tebang.generate-confirm', compact('vendor', 'hasilTebangs', 'kodeBAPP', 'totalPendapatan'));
    // }

    // public function generateBapp(Request $request)
    // {
    //     $request->validate([
    //         'kode_bapp' => 'required',
    //         'tanggal_bapp' => 'required|date',
    //         'periode' => 'required',
    //         'vendor_kode' => 'required',
    //         'hasil_tebang_ids' => 'required|array|min:1'
    //     ]);

    //     $bapp = BappTebang::create([
    //         'kode_bapp' => $request->kode_bapp,
    //         'tanggal_bapp' => $request->tanggal_bapp,
    //         'periode' => $request->periode,
    //         'vendor_kode' => $request->vendor_kode,
    //         'keterangan' => $request->keterangan,
    //     ]);

    //     HasilTebang::whereIn('id', $request->hasil_tebang_ids)
    //         ->update(['status' => 'generated', 'kode_bapp' => $bapp->kode_bapp]);

    //     return redirect()->route('hasil-tebang.generate-selection', $request->vendor_kode)
    //         ->with('success', 'BAPP berhasil dibuat.');
    // }

    public function editForm($id)
    {
        $hasil = HasilTebang::with(['vendorTebang', 'vendorAngkut'])->findOrFail($id);
        $vendors = VendorAngkut::all();
        $lkts = LKT::all();
        return view('hasiltebang.edit-form', compact('hasil', 'vendors', 'lkts'));
    }
}
