<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Vehicle::with('vendorAngkut')
            ->when($this->search, function($query) {
                $search = $this->search;
                $query->where('kode_lambung', 'like', "%{$search}%")
                      ->orWhere('plat_nomor', 'like', "%{$search}%")
                      ->orWhereHas('vendorAngkut', function($q) use ($search) {
                          $q->where('nama_vendor', 'like', "%{$search}%");
                      });
            })
            ->orderBy('kode_vendor')
            ->orderBy('kode_lambung')
            ->get();

        return $query;
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->kode_vendor,
            $vehicle->vendorAngkut ? $vehicle->vendorAngkut->nama_vendor : '-',
            $vehicle->kode_lambung,
            $vehicle->plat_nomor,
            $vehicle->jenis_unit,
            $vehicle->created_at ? $vehicle->created_at->format('d/m/Y H:i') : '-',
            $vehicle->updated_at ? $vehicle->updated_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Vendor',
            'Nama Vendor',
            'Kode Lambung',
            'Plat Nomor',
            'Jenis Unit',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }
}
