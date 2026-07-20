<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('late_held_requests', function (Blueprint $table) {
            $table->string('request_type', 30)->default('late_held')->after('held_date');
            $table->unsignedBigInteger('substitute_teacher_id')->nullable()->after('teacher_id');
            $table->index(['substitute_teacher_id', 'status'], 'late_held_substitute_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('late_held_requests', function (Blueprint $table) {
            $table->dropIndex('late_held_substitute_status_index');
            $table->dropColumn(['request_type', 'substitute_teacher_id']);
        });
    }
};
