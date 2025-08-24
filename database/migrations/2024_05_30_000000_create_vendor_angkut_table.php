<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorAngkutTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_angkut', function (Blueprint $table) {
            $table->id();
            $table->string('kode_vendor')->unique();
            $table->string('nama_vendor');
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('bank')->nullable();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_angkut');
    }
}
