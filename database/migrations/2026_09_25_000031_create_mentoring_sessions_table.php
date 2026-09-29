<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('mentoring_requests')->cascadeOnDelete();
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->dateTime('started_at')->useCurrent();
            $table->dateTime('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_sessions');
    }
};
