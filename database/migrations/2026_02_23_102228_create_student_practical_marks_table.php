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
        Schema::create('student_practical_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('teacher_id');

            $table->integer('continuous_assessment')->nullable();
            $table->integer('end_sem_practical')->nullable();
            $table->integer('viva_voce')->nullable();
            $table->integer('total_marks')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'paper_id']); // prevent duplicate entry
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_practical_marks');
    }
};
