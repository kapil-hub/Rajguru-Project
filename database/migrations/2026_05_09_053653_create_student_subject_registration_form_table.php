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
        Schema::create('student_subject_registration_form', function (Blueprint $table) {
            $table->id();
            $table->integer("student_user_id");
            $table->integer("paper_master_id");
            $table->string("semester");
            $table->string("academic_year");
            $table->tinyInteger("is_backlog")->default(0);
            $table->tinyInteger("is_approved")->default(0);
            $table->integer("approved_by_id")->nullable();
            $table->integer("approved_by_type")->nullable();
            $table->date("approved_date")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_registration_form');
    }
};
