<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_applicants', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_id')->unique();
            $table->string('full_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email');
            $table->string('university');
            $table->json('tech_skills');
            $table->json('soft_skills');
            $table->string('gender');
            $table->string('gender_other_specify')->nullable();
            $table->string('phone_number');
            $table->string('profile_picture')->nullable();
            $table->string('emergency_contact');
            $table->string('emergency_contact_number', 11)->nullable();
            $table->string('resume_path')->nullable();
            $table->timestamp('privacy_acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_applicants');
    }
};
