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
        Schema::create('student_papers_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_student_paper_id')->nullable()->index();
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedBigInteger('paper_master_id');
            $table->string('semester');
            $table->string('academic_year');
            $table->boolean('is_backlog')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_papers_history');
    }
};