<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotes_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('psikotes_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('psikotes_questions')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('psikotes_options')->cascadeOnDelete();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotes_answers');
    }
};
