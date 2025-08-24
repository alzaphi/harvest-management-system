<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Console\Command;

class GenerateForemanSubBlocks extends Command
{
    protected $signature = 'generate:foreman-subblocks';


    protected $description = 'Generate data foreman_sub_blocks dari harvest_sub_blocks dan assign ke mandor berdasarkan luas area';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mandors = DB::table('foreman')->get();

        // Join harvest_sub_blocks dan sub_blocks untuk ambil kode_petak, divisi, dan luas_area
        $subblocks = DB::table('harvest_sub_blocks')
            ->join('sub_blocks', 'harvest_sub_blocks.kode_petak', '=', 'sub_blocks.kode_petak')
            ->select(
                'harvest_sub_blocks.kode_petak',
                'harvest_sub_blocks.divisi',
                'sub_blocks.luas_area'
            )
            ->get();

        // Validasi awal
        if ($mandors->isEmpty()) {
            $this->error('❌ Data mandor kosong.');
            return 1;
        }

        if ($subblocks->isEmpty()) {
            $this->error('❌ Data subblocks kosong.');
            return 1;
        }

        // Persiapan pembagian
        $assignments = [];
        $mandorIndex = 0;
        $mandorCount = count($mandors);
        $petakCountForSmall = 0;

        foreach ($subblocks as $subblock) {
            $luas = (float) $subblock->luas_area;
            $mandor = $mandors[$mandorIndex % $mandorCount];

            $assignments[] = [
                'kode_petak' => $subblock->kode_petak,
                'divisi' => $subblock->divisi, // LANGSUNG dari harvest_sub_blocks
                'kode_mandor' => $mandor->kode_mandor,
                'nama_mandor' => $mandor->nama_mandor,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Atur rotasi mandor berdasarkan luas area
            if ($luas > 3) {
                $mandorIndex++; // 1 mandor untuk 1 petak besar
            } elseif ($luas < 2) {
                $petakCountForSmall++;
                if ($petakCountForSmall >= 3) {
                    $mandorIndex++; // max 3 petak kecil
                    $petakCountForSmall = 0;
                }
            } else {
                $mandorIndex++; // petak sedang → 1 mandor
            }
        }

        // Optional: hapus data sebelumnya dulu
        DB::table('foreman_sub_blocks')->truncate();

        // Insert data baru
        DB::table('foreman_sub_blocks')->insert($assignments);

        $this->info('✔️ Berhasil generate ' . count($assignments) . ' foreman_sub_blocks.');
        return 0;
    }
}
