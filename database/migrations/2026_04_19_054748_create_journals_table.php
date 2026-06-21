<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id('journal_id');
            $table->unsignedBigInteger('intern_id');
            $table->enum('journal_type', ['daily', 'weekly'])->default('daily');
            $table->string('title', 100);
            $table->text('content');
            $table->json('task_ids')->nullable();
            $table->text('notes')->nullable();
            $table->date('entry_date');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('intern_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
