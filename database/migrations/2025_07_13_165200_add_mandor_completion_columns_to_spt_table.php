<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMandorCompletionColumnsToSptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spt', function (Blueprint $table) {
            $table->timestamp('completed_by_mandor_at')->nullable()->after('status');
            $table->unsignedBigInteger('completed_by_mandor_id')->nullable()->after('completed_by_mandor_at');
            
            // Add foreign key constraint
            $table->foreign('completed_by_mandor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spt', function (Blueprint $table) {
            $table->dropForeign(['completed_by_mandor_id']);
            $table->dropColumn(['completed_by_mandor_at', 'completed_by_mandor_id']);
        });
    }
}
