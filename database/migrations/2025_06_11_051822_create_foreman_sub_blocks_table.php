<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForemanSubBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foreman_sub_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petak');
            $table->string('divisi');
            $table->string('kode_mandor', 10);
            $table->string('nama_mandor', 100);
            $table->date('tanggal_kerja');
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('kode_petak')
                  ->references('kode_petak')
                  ->on('sub_blocks')
                  ->onDelete('cascade');
            
            // Add index for better performance
            $table->index(['kode_petak', 'kode_mandor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foreman_sub_blocks');
    }
}
