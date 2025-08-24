<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeMandorToForemanTable extends Migration
{
    public function up()
    {
        Schema::table('foreman', function (Blueprint $table) {
            $table->string('kode_mandor', 20)->after('id')->unique();
        });
    }

    public function down()
    {
        Schema::table('foreman', function (Blueprint $table) {
            $table->dropColumn('kode_mandor');
        });
    }
}
