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
        Schema::table('paper_master', function (Blueprint $table) {
            $table->text("eligibilty")->nullable();
            $table->text("prereqisites")->nullable();
            $table->integer("capping")->nullable();
            $table->text("remark")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_master', function (Blueprint $table) {
            //
        });
    }
};
