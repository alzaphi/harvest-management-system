<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('maps')) {
            Schema::create('maps', function (Blueprint $table) {
                $table->id();
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->string('file_type', 50);
                $table->string('uploaded_by', 100)->nullable();
                $table->string('estate_name', 100)->nullable();
                $table->text('description')->nullable();
                $table->dateTime('upload_date')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Don't drop the table in production
        if (app()->environment('local', 'testing')) {
            Schema::dropIfExists('maps');
        }
    }
}
