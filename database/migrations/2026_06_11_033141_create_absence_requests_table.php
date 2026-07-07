<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['absence', 'early_leave'])->default('absence');
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('supervisor_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Prevent multiple requests for the same date by the same intern
            $table->unique(['intern_id', 'date']);
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
