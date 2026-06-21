<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('system_backups');

        Schema::create('system_backups', function (Blueprint $table) {
            $table->id('backup_id');
            $table->unsignedBigInteger('admin_id')->nullable(); // Nullable for automated backups
            $table->enum('backup_type', ['manual', 'auto'])->default('manual');
            $table->string('file_path', 255)->nullable();
            $table->string('file_size')->nullable(); // Store formatted size (e.g., 2.5 MB)
            $table->enum('status', ['success', 'failed', 'in_progress'])->default('in_progress');
            $table->string('sha256_hash', 64)->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
