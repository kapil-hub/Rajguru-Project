<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_held_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('timetable_held_pools', 'batch_identifier')) {
                $table->string('batch_identifier', 100)->default('')->after('section');
            }
        });

        DB::table('timetable_held_pools')
            ->whereNull('batch_identifier')
            ->update(['batch_identifier' => '']);

        Schema::table('timetable_held_pools', function (Blueprint $table) {
            $table->dropUnique('held_pool_unique');
            $table->unique(
                ['teacher_id', 'course_id', 'semester_id', 'paper_master_id', 'section', 'batch_identifier', 'month', 'year'],
                'held_pool_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('timetable_held_pools', function (Blueprint $table) {
            $table->dropUnique('held_pool_unique');
            $table->unique(
                ['teacher_id', 'course_id', 'semester_id', 'paper_master_id', 'section', 'month', 'year'],
                'held_pool_unique'
            );

            if (Schema::hasColumn('timetable_held_pools', 'batch_identifier')) {
                $table->dropColumn('batch_identifier');
            }
        });
    }
};
