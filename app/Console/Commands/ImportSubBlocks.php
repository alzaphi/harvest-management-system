<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSubBlocks extends Command
{
    protected $signature = 'import:subblocks {path=storage/app/jbm.geojson}';
    protected $description = 'Import data GeoJSON ke tabel sub_blocks';

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::exists($path)) {
            $this->error("File tidak ditemukan: $path");
            return 1;
        }

        $data = json_decode(File::get($path), true);

        if (!$data || !isset($data['features'])) {
            $this->error("Format GeoJSON tidak valid.");
            return 1;
        }

        foreach ($data['features'] as $feature) {
            $p = $feature['properties'];
            $geom = json_encode($feature['geometry']);

            $estate = $p['unit_kbn'] ?? null;
            if ($estate === 'LKL') {
                $zona = 1;
            } elseif ($estate === 'PLG') {
                $zona = rand(2, 3);
            } elseif ($estate === 'RST') {
                $zona = 4;
            } else {
                $zona = null;
            }


            DB::table('sub_blocks')->insert([
                'kode_petak' => $p['sub_petak'] ?? null,
                'estate'     => $estate,
                'divisi'     => $p['divisi'] ?? null,
                'blok'       => $p['blok'] ?? null,
                'luas_area'  => $p['luas_area_ha'] ?? null, // pastikan sudah diisi via Python sebelumnya
                'geom_json'  => $geom,
                'aktif'      => 1,
                'zona'       => $zona,
                'keterangan' => 'petak jbm',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('✅ Import berhasil ke tabel sub_blocks.');
        return 0;
    }
}
