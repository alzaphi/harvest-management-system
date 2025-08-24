<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status values to match the new format
        DB::table('tracking_activity')
            ->where('status_tracking', 'waiting')
            ->update(['status_tracking' => 'Not Started']);

        DB::table('tracking_activity')
            ->where('status_tracking', 'on_process')
            ->update(['status_tracking' => 'In Progress']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the old status values if needed
        DB::table('tracking_activity')
            ->where('status_tracking', 'Not Started')
            ->update(['status_tracking' => 'waiting']);

        DB::table('tracking_activity')
            ->where('status_tracking', 'In Progress')
            ->update(['status_tracking' => 'on_process']);
    }
};
