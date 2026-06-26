<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            
            // Header Image assets
            $table->string('logo_path')->nullable();
            $table->string('footer_path')->nullable();

            // Header labels
            $table->string('gov_text')->default('Republic of the Philippines');
            $table->string('dept_text')->default('Department of Education');
            $table->string('region_text')->default('NATIONAL CAPITAL REGION');
            $table->string('office_text')->default('SCHOOLS DIVISION OFFICE QUEZON CITY');
            
            // Certificate Title & Body Template
            $table->string('title_text')->default('CERTIFICATE OF COMPLETION');
            $table->text('body_text')->default('This is to certify that {name} {article} {course} student of {school}, has completed {hours} hours On-the-Job Training on {start_date} to {end_date} at the Schools Division Office-Quezon City, {unit}.');

            // Bottom Section (Signatory / Initials)
            $table->string('signatory_name')->default('MICHELLE A. MAL-IN');
            $table->string('signatory_title')->default('Administrative Officer IV');
            $table->string('initials')->default('HRNTP/rocelle/Pj');

            // Custom HTML Template Upload
            $table->longText('html_layout')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
