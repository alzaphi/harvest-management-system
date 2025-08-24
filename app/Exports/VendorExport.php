<?php

namespace App\Exports;

use App\Models\Vendor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VendorExport implements FromCollection, WithHeadings, WithMapping
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
        $query = Vendor::query();

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
            if ($this->jenis_vendor === 'both') {
                // Get vendors that have the same name, no_hp, nomor_rekening, and nama_bank
                $groupedVendors = Vendor::select('nama_vendor', 'no_hp', 'nomor_rekening', 'nama_bank')
                    ->groupBy('nama_vendor', 'no_hp', 'nomor_rekening', 'nama_bank')
                    ->havingRaw('COUNT(*) > 1')
                    ->pluck('nama_vendor');
                
                $query->whereIn('nama_vendor', $groupedVendors);
            } elseif ($this->jenis_vendor === 'angkut') {
                $query->where('kode_vendor', 'LIKE', 'VA%');
            } elseif ($this->jenis_vendor === 'tebang') {
                $query->where('kode_vendor', 'LIKE', 'VT%');
            }
        }

        return $query->orderBy('nama_vendor')->get();
    }

    /**
     * Map the data for the Excel file
     */
    public function map($vendor): array
    {
        return [
            $vendor->kode_vendor,
            $vendor->nama_vendor,
            $vendor->no_hp,
            $vendor->jenis_vendor,
            $vendor->status,
            $vendor->nomor_rekening,
            $vendor->nama_bank,
            $this->formatDate($vendor->created_at),
            $this->formatDate($vendor->updated_at),
        ];
    }
    
    /**
     * Format date safely
     */
    protected function formatDate($date)
    {
        if (!$date) {
            return '';
        }
        
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('d/m/Y H:i');
        }
        
        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
            } catch (\Exception $e) {
                return $date; // Return as is if can't parse
            }
        }
        
        return '';
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'Kode Vendor',
            'Nama Vendor',
            'No. HP',
            'Jenis Vendor',
            'Status',
            'Nomor Rekening',
            'Nama Bank',
            'Dibuat Pada',
            'Diperbarui Pada',
        ];
    }
}
