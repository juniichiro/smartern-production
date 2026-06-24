<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('certificate_id');

            // Foreign Keys
            $table->unsignedBigInteger('intern_id');
            $table->unsignedBigInteger('supervisor_id'); // Office Supervisor
            $table->unsignedBigInteger('head_supervisor_id')->nullable();

            // Certificate Workflow Status (pending → endorsed → published)
            $table->enum('status', ['pending', 'endorsed', 'published'])->default('pending');

            // Supervisor Verification Status (pending → verified → rejected)
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');

            // Certificate Data
            $table->integer('completed_hours')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('issue_date')->nullable();

            // Intern Unit (Where the intern completed their internship, e.g., "IT Department", "HR Department")
            $table->string('intern_unit')->nullable();

            // Snapshot of intern details at the time of certificate issuance
            $table->string('intern_name')->nullable();
            $table->string('intern_course')->nullable();
            $table->string('intern_school')->nullable();

            // File & Security
            $table->string('file_path')->nullable();
            $table->string('verification_code')->unique()->nullable();
            $table->text('supervisor_feedback')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('intern_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('head_supervisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
