<?php

namespace App\Exports;

use App\Models\VendorAngkut;
use Illuminate\Support\Collection;

class VendorAngkutExport
{
    protected $search;
    protected $jenis_vendor;

    public function __construct($search = null, $jenis_vendor = null)
    {
        $this->search = $search;
        $this->jenis_vendor = $jenis_vendor;
    }

    /**
     * Get the data collection
     * @return Collection
     */
    public function collection()
    {
        $query = VendorAngkut::query();

        // Apply search filter
        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_vendor', 'like', "%{$search}%")
                  ->orWhere('nama_vendor', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Apply vendor type filter
        if ($this->jenis_vendor) {
            if ($this->jenis_vendor === 'Vendor Angkut & Tebang') {
                // Get vendors that have the same name, no_hp, nomor_rekening, and nama_bank
                $groupedVendors = VendorAngkut::select('nama_vendor', 'no_hp', 'nomor_rekening', 'nama_bank')
                    ->groupBy('nama_vendor', 'no_hp', 'nomor_rekening', 'nama_bank')
                    ->havingRaw('COUNT(*) > 1')
                    ->pluck('nama_vendor');
                
                $query->whereIn('nama_vendor', $groupedVendors);
            } else {
                $query->where('jenis_vendor', $this->jenis_vendor);
            }
        }

        // Apply sorting
        if ($this->jenis_vendor === 'Vendor Angkut & Tebang') {
            $query->orderBy('nama_vendor', 'asc')
                  ->orderBy('kode_vendor', 'asc');
        } else {
            $query->orderBy('kode_vendor', 'asc');
        }

        return $query->select(
            'kode_vendor', 
            'nama_vendor', 
            'no_hp', 
            'jenis_vendor', 
            'status', 
            'nomor_rekening', 
            'nama_bank',
            'created_at',
            'updated_at'
        )->get();
    }

    /**
     * Get the column headings for the Excel file
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Vendor',
            'Nama Vendor',
            'No HP',
            'Jenis Vendor',
            'Status',
            'No Rekening',
            'Nama Bank',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    /**
     * Map the data for the Excel file
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->kode_vendor,
            $row->nama_vendor,
            $row->no_hp,
            $row->jenis_vendor,
            $row->status,
            $row->nomor_rekening,
            $row->nama_bank,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
