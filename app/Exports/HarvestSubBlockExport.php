<?php

namespace App\Exports;

use App\Models\HarvestSubBlock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HarvestSubBlockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    /**
     * @var Collection
     */
    protected $harvestSubBlocks;

    /**
     * @param Collection $harvestSubBlocks
     */
    public function __construct($harvestSubBlocks)
    {
        $this->harvestSubBlocks = $harvestSubBlocks;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->harvestSubBlocks;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Petak',
            'Estate',
            'Divisi',
            'Luas Area (Ha)',
            'Musim Panen',
            'Umur (Bln)',
            'Estimasi (Ton/Ha)',
            'Rencana Panen',
            'Prioritas',
            'Keterangan',
        ];
    }

    /**
     * @param mixed $harvest
     * @return array
     */
    public function map($harvest): array
    {
        return [
            $harvest->id,
            $harvest->kode_petak,
            $harvest->subBlock->estate ?? '-',
            $harvest->subBlock->divisi ?? '-',
            $harvest->subBlock->luas_area ? number_format($harvest->subBlock->luas_area, 2) : '0.00',
            $harvest->harvest_season,
            $harvest->age_months,
            number_format($harvest->yield_estimate_tph, 2),
            $harvest->planned_harvest_date ? \Carbon\Carbon::parse($harvest->planned_harvest_date)->format('d-m-Y') : '-',
            $harvest->priority_level,
            $harvest->remarks ?? '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
