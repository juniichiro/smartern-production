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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('school_name')->nullable();
            $table->string('type'); // holiday, suspension, work_day, school_default
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('daily_hours')->nullable();
            $table->enum('rounding_mode', ['none', 'floor', 'ceil'])->default('none');
            $table->enum('late_policy', ['actual', 'next_hour', 'next_half_hour'])->default('actual');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
