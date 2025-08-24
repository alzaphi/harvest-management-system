<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateVehicleJenisUnit extends Migration
{
    public function up()
    {
        // Add the jenis_unit_id column
        Schema::table('vehicle', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_unit_id')->nullable()->after('jenis_unit');
            
            // Add foreign key constraint
            $table->foreign('jenis_unit_id')
                  ->references('id')
                  ->on('jenis_unit')
                  ->onDelete('set null');
        });

        // Map existing enum values to jenis_unit records
        $mapping = [
            'Pickup' => 'PICKUP',
            'Truck' => 'TRUCK',
        ];

        foreach ($mapping as $oldValue => $kodeUnit) {
            $jenisUnit = DB::table('jenis_unit')
                ->where('kode_unit', $kodeUnit)
                ->first();
                
            if ($jenisUnit) {
                DB::table('vehicle')
                    ->where('jenis_unit', $oldValue)
                    ->update(['jenis_unit_id' => $jenisUnit->id]);
            }
        }

        // Make the column not nullable after data migration
        Schema::table('vehicle', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_unit_id')->nullable(false)->change();
        });

        // Drop the old column (only after confirming data is migrated correctly)
        // Schema::table('vehicle', function (Blueprint $table) {
        //     $table->dropColumn('jenis_unit');
        // });
    }

    public function down()
    {
        // Add the old column back if needed
        // Schema::table('vehicle', function (Blueprint $table) {
        //     $table->enum('jenis_unit', ['Pickup', 'Truck'])->after('plat_nomor');
        // });

        // Copy data back if needed
        // $mapping = [
        //     1 => 'Pickup',
        //     2 => 'Truck',
        // ];
        
        // foreach ($mapping as $id => $value) {
        //     DB::table('vehicle')
        //         ->where('jenis_unit_id', $id)
        //         ->update(['jenis_unit' => $value]);
        // }

        // Drop the foreign key and column
        Schema::table('vehicle', function (Blueprint $table) {
            $table->dropForeign(['jenis_unit_id']);
            $table->dropColumn('jenis_unit_id');
        });
    }
}
