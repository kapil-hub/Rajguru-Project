<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_held_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('paper_timetable_id');
            $table->date('held_date');
            $table->string('status', 20)->default('pending');
            $table->text('reason')->nullable();
            $table->text('tic_remark')->nullable();
            $table->unsignedBigInteger('action_by')->nullable();
            $table->timestamp('action_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'paper_timetable_id', 'held_date'], 'late_held_unique');
            $table->index(['department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_held_requests');
    }
};
