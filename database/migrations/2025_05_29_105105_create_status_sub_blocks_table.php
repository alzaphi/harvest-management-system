<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusSubBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_sub_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petak');
            $table->dateTime('tanggal_update');
            $table->string('tahun');
            $table->string('status');
            $table->decimal('luas_status', 10, 2);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            // Add foreign key to sub_blocks table
            $table->foreign('kode_petak')
                  ->references('kode_petak')
                  ->on('sub_blocks')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_sub_blocks');
    }
}
