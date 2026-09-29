<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->text('kualifikasi')->nullable();
            $table->string('lokasi', 200)->nullable();
            $table->enum('tipe', ['fulltime', 'parttime', 'magang', 'kontrak', 'freelance'])->default('fulltime');
            $table->string('kategori', 100)->nullable();
            $table->date('deadline')->nullable();
            $table->string('apply_url', 500)->nullable();
            $table->string('poster')->nullable();
            $table->string('kontak_hr_nama', 150)->nullable();
            $table->string('kontak_hr_telp', 30)->nullable();
            $table->enum('status', ['aktif', 'expired', 'draft'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'deadline']);
            $table->fullText(['judul', 'deskripsi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
