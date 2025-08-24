<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        // Array of vendor IDs (10 out of 16 vendors)
        $vendorIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        
        // Array of vehicle types
        $vehicleTypes = [
            'Truck', 'Trailer', 'Tanker', 'Dump Truck', 'Excavator', 'Bulldozer',
            'Forklift', 'Crane', 'Tractor', 'Loader'
        ];

        // Generate vehicles for each vendor
        foreach ($vendorIds as $vendorId) {
            // Generate 3 vehicles per vendor
            for ($i = 1; $i <= 3; $i++) {
                $vehicle = new Vehicle();
                $vehicle->vendor_angkut_id = $vendorId;
                $vehicle->kode_lambung = 'JBM-24-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $vehicle->plat_nomor = 'B ' . rand(1000, 9999) . ' ' . Str::random(3);
                $vehicle->jenis_unit = $vehicleTypes[rand(0, count($vehicleTypes) - 1)];
                $vehicle->save();
            }
        }
    }
}
