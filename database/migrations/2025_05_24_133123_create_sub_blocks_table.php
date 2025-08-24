<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petak');
            $table->string('estate');
            $table->string('divisi');
            $table->string('blok');
            $table->decimal('luas_area', 8, 2);
            $table->boolean('aktif')->default(true);
            $table->string('zona');
            $table->text('keterangan')->nullable();
            $table->double('shape_length')->nullable();
            $table->double('shape_area')->nullable();
            $table->geometry('geometry')->nullable();
            $table->timestamps();
        });

        // Add spatial index for better performance with spatial queries
        DB::statement('ALTER TABLE sub_blocks ADD SPATIAL INDEX(geometry)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_blocks');
    }
}
