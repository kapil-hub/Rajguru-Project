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
        Schema::table('paper_timetables', function (Blueprint $table) {
            $table->boolean('is_lecture')->nullable()->after('paper_id');
            $table->boolean('is_tutorial')->nullable()->after('is_lecture');
            $table->boolean('is_practical')->nullable()->after('is_tutorial');
            $table->boolean('is_coordinator')->nullable()->after('is_practical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_timetables', function (Blueprint $table) {
            $table->dropColumn(['is_lecture', 'is_tutorial', 'is_practical', 'is_coordinator']);
        });
    }
};
