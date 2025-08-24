<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHarvestSubBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('harvest_sub_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petak', 50);
            $table->string('estate', 100);
            $table->string('divisi', 100);
            $table->double('luas_area');
            $table->string('harvest_season', 20)->nullable();
            $table->integer('age_months')->nullable();
            $table->decimal('yield_estimate_tph', 6, 2)->nullable();
            $table->date('planned_harvest_date')->nullable();
            $table->integer('priority_level')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Add index for better performance
            $table->index('kode_petak');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('harvest_sub_blocks');
    }
}
