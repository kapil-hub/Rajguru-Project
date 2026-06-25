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
        Schema::table('student_papers', function (Blueprint $table) {
            $table->string('batch', 10)->nullable()->after('is_backlog');
        });

        Schema::table('paper_timetables', function (Blueprint $table) {
            $table->string('batches', 255)->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_papers', function (Blueprint $table) {
            $table->dropColumn('batch');
        });

        Schema::table('paper_timetables', function (Blueprint $table) {
            $table->dropColumn('batches');
        });
    }
};
