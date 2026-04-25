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
        Schema::table('ia_marks', function (Blueprint $table) {
            $table->decimal('tute_attendance', 5, 2)->nullable()->after("tute_ca");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ia_marks', function (Blueprint $table) {
            //
        });
    }
};
