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
        Schema::create('intern_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('office_id')->constrained('offices');
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('course', 100);
            $table->string('school', 100);
            $table->string('profile_picture')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('gender_other_specify', 50)->nullable();
            $table->string('phone_number', 15);
            $table->string('emergency_contact', 100)->nullable();
            $table->string('emergency_contact_number', 20)->nullable();
            $table->string('schedule_assignment')->nullable();
            $table->decimal('hours_rendered', 5, 2)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_details');
    }
};
