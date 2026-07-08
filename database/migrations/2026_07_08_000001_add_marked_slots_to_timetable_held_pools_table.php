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
        Schema::table('timetable_held_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('timetable_held_pools', 'marked_slots')) {
                $table->json('marked_slots')->nullable()->after('student_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_held_pools', function (Blueprint $table) {
            if (Schema::hasColumn('timetable_held_pools', 'marked_slots')) {
                $table->dropColumn('marked_slots');
            }
        });
    }
};
