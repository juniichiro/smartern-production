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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Following the consistent foreignId structure
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('intern_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('office_id')->constrained('offices')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->dateTime('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'revision', 'completed'])->default('pending');
            $table->boolean('is_file_required')->default(true);
            $table->string('file_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
