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
        Schema::create('student_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("paper_master_id");
            $table->integer("student_user_id");
            $table->integer("log_count");
            $table->text("remark")->nullable();
            $table->text("created_by_auth_type");
            $table->text("created_by_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_logs');
    }
};
