<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apar_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_user_id');
            $table->string('academic_year', 9);
            $table->string('status', 24)->default('draft');
            $table->json('form_data');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->string('hod_teaching_grade', 32)->nullable();
            $table->string('hod_b2_grade', 32)->nullable();
            $table->string('hod_overall_grade', 32)->nullable();
            $table->timestamps();

            $table->unique(['faculty_user_id', 'academic_year']);
            $table->index(['academic_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apar_submissions');
    }
};
