<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_theory_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('teacher_id');
            $table->decimal('theory_marks', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'paper_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_theory_marks');
    }
};
