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
        Schema::create('timetable_held_pools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('paper_master_id');
            $table->string('section', 10);
            $table->integer('month');
            $table->integer('year');
            $table->integer('lecture_held')->default(0);
            $table->integer('tute_held')->default(0);
            $table->integer('practical_held')->default(0);
            $table->json('student_ids')->nullable();
            $table->json('marked_slots')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'course_id', 'semester_id', 'paper_master_id', 'section', 'month', 'year'], 'held_pool_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_held_pools');
    }
};
