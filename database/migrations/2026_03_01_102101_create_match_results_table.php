<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_name');
            $table->string('school');
            $table->foreignId('office_id')->constrained('offices')->onDelete('cascade');
            $table->integer('score');
            $table->string('confidence')->default('Low');
            $table->string('status');
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('requested_by_supervisor')->default(false);
            $table->text('breakdown')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
