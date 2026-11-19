<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_practical_marks', function (Blueprint $table) {
            $table->decimal('continuous_assessment', 5, 2)->nullable()->change();
            $table->decimal('end_sem_practical', 5, 2)->nullable()->change();
            $table->decimal('viva_voce', 5, 2)->nullable()->change();
            $table->decimal('total_marks', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_practical_marks', function (Blueprint $table) {
            $table->integer('continuous_assessment')->nullable()->change();
            $table->integer('end_sem_practical')->nullable()->change();
            $table->integer('viva_voce')->nullable()->change();
            $table->integer('total_marks')->nullable()->change();
        });
    }
};
