<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('onboarding_applicants')->onDelete('cascade');
            $table->foreignId('head_supervisor_id')->constrained('users')->onDelete('cascade');
            $table->text('interview_notes')->nullable();
            $table->json('verified_skills')->nullable();
            $table->integer('behavioral_score')->default(3);
            $table->boolean('is_recommended')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_evaluations');
    }
};
