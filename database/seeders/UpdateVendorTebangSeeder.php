<?php

namespace Database\Seeders;

use App\Models\VendorTebang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateVendorTebangSeeder extends Seeder
{
    public function run()
    {
        // Update all existing vendor tebang records
        DB::table('vendor_tebang')
            ->update(['jenis_vendor' => 'Vendor Tebang']);
            
        $this->command->info('Successfully updated vendor tebang records!');
    }
}
