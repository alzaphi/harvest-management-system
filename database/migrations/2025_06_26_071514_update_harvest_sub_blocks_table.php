<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHarvestSubBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_sub_blocks', function (Blueprint $table) {
            // Drop existing columns that are no longer needed
            $table->dropColumn(['tanggal_panen', 'jumlah_panen', 'satuan', 'keterangan']);

            // Add new columns
            $table->string('estate', 100)->after('kode_petak');
            $table->string('divisi', 100)->after('estate');
            $table->decimal('luas_area', 10, 2)->after('divisi');
            $table->string('harvest_season', 20)->after('luas_area');
            $table->integer('age_months')->after('harvest_season');
            $table->decimal('yield_estimate_tph', 8, 2)->after('age_months');
            $table->date('planned_harvest_date')->after('yield_estimate_tph');
            $table->integer('priority_level')->default(1)->after('planned_harvest_date');
            $table->text('remarks')->nullable()->after('priority_level');

            // Add foreign key constraint
            $table->foreign('kode_petak')
                  ->references('kode_petak')
                  ->on('sub_blocks')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('harvest_sub_blocks', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['kode_petak']);

            // Drop new columns
            $table->dropColumn([
                'estate',
                'divisi',
                'luas_area',
                'harvest_season',
                'age_months',
                'yield_estimate_tph',
                'planned_harvest_date',
                'priority_level',
                'remarks'
            ]);

            // Re-add original columns
            $table->date('tanggal_panen');
            $table->decimal('jumlah_panen', 10, 2);
            $table->string('satuan')->default('kg');
            $table->text('keterangan')->nullable();
        });
    }
}
