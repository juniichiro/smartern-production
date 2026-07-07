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
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // e.g., "I. Digital and Technical Skills"
            $table->text('competency_text');
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_items');
    }
};
