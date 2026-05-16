<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_assignments', function (Blueprint $table) {

            $table->id();

            $table->string('auth_type');

            $table->unsignedBigInteger('auth_id');

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'auth_type',
                'auth_id',
                'role_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
    }
};