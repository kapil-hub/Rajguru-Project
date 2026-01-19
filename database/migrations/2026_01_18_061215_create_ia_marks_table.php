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
        Schema::create('ia_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('paper_master_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('section');

            $table->decimal('tute_ca', 5, 2)->nullable();
            $table->decimal('class_test', 5, 2)->nullable();
            $table->decimal('assignment', 5, 2)->nullable();
            $table->decimal('attendance', 5, 2)->nullable();
            $table->decimal('total', 5, 2);
            $table->decimal("total_tute_marks", 5, 2)->nullable();
            $table->decimal("grand_total", 5, 2)->nullable();
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ia_marks');
    }
};
