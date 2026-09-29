<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('sekolah', 150)->nullable();
            $table->year('angkatan')->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('foto')->nullable();
            $table->text('bio')->nullable();
            $table->enum('status_karier', ['bekerja', 'wirausaha', 'pendidikan', 'lainnya'])->nullable();
            $table->timestamps();
            $table->index('status_karier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_profiles');
    }
};
