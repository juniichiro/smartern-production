<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_id');
            $table->string('technical_proficiency')->nullable();
            $table->string('professionalism_ethics')->nullable();
            $table->string('communication_skills')->nullable();
            $table->string('team_collaboration')->nullable();
            $table->timestamps();

            $table->foreign('certificate_id')->references('certificate_id')->on('certificates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
