<?php

namespace App\Exports;

use App\Models\VendorTebang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorTebangExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return VendorTebang::select([
            'kode_vendor',
            'nama_vendor',
            'no_hp',
            'jenis_vendor',
            'status',
            'created_at',
            'updated_at'
        ])->get()->map(function ($vendor, $index) {
            return [
                $index + 1,
                $vendor->kode_vendor,
                $vendor->nama_vendor,
                $vendor->no_hp,
                $vendor->jenis_vendor,
                $vendor->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Vendor',
            'Nama Vendor',
            'No HP',
            'Jenis Vendor',
            'Status',
        ];
    }
}