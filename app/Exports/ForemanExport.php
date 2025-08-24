<?php

namespace App\Exports;

use App\Models\Foreman;
use Illuminate\Support\Collection;

class ForemanExport
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = \App\Models\Foreman::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_mandor', 'like', "%{$search}%")
                  ->orWhere('nama_mandor', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('kode_mandor', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Mandor',
            'Nama Mandor',
            'Email',
            'No HP',
            'Status',
            'Dibuat Pada',
            'Diperbarui Pada',
        ];
    }

    public function map($foreman): array
    {
        return [
            $foreman->kode_mandor,
            $foreman->nama_mandor,
            $foreman->email,
            $foreman->no_hp,
            $foreman->status,
            $foreman->created_at ? $foreman->created_at->format('d/m/Y H:i') : '-',
            $foreman->updated_at ? $foreman->updated_at->format('d/m/Y H:i') : '-',
        ];
    }
}
