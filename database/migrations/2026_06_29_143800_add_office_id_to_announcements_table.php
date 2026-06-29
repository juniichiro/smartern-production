<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('cascade');
        });

        // Populate existing announcements with their creator's office_id so they remain office-specific
        $announcements = DB::table('announcements')->get();
        foreach ($announcements as $announcement) {
            $supervisor = DB::table('supervisor_details')->where('user_id', $announcement->user_id)->first();
            if ($supervisor) {
                DB::table('announcements')
                    ->where('id', $announcement->id)
                    ->update(['office_id' => $supervisor->office_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });
    }
};
