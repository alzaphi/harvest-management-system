<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForemanTable extends Migration
{
    public function up()
    {
        Schema::create('foreman', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mandor');
            $table->string('email')->unique();
            $table->string('no_hp')->nullable();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('foreman');
    }
}
