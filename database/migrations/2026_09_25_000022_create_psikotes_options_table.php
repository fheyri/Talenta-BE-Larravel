<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotes_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('psikotes_questions')->cascadeOnDelete();
            $table->string('teks', 500);
            $table->integer('skor')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotes_options');
    }
};
