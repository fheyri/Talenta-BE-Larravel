<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotes_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('psikotes_tests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_ke')->default(1);
            $table->unsignedInteger('token_used')->default(0);
            $table->enum('status', ['berjalan', 'selesai', 'kedaluwarsa'])->default('berjalan');
            $table->dateTime('started_at')->useCurrent();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->integer('total_skor')->nullable();
            $table->text('hasil')->nullable();
            $table->unique(['user_id', 'test_id', 'attempt_ke']);
            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotes_attempts');
    }
};
