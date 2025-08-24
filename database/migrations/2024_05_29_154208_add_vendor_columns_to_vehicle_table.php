<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicle', function (Blueprint $table) {
            $table->string('kode_vendor')->nullable()->after('vendor_angkut_id');
            $table->string('nama_vendor')->nullable()->after('kode_vendor');
        });
    }

    public function down()
    {
        Schema::table('vehicle', function (Blueprint $table) {
            $table->dropColumn(['kode_vendor', 'nama_vendor']);
        });
    }
};
