<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('kode_vendor');
            $table->string('kode_lambung')->nullable()->unique();
            $table->string('plat_nomor')->nullable()->unique();
            $table->enum('jenis_unit', ['Pickup', 'Truck']);
            $table->foreignId('vendor_angkut_id')->constrained('vendor_angkut')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle');
    }
}
