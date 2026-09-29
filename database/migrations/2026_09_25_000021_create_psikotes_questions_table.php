<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotes_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('psikotes_tests')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->unsignedInteger('urutan')->default(1);
            $table->index(['test_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotes_questions');
    }
};
