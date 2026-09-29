<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa_profiles')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->enum('level', ['pemula', 'menengah', 'mahir'])->default('pemula');
            $table->unique(['siswa_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_skills');
    }
};
