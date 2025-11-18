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
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrer_id')->constrained()->onDelete('cascade'); // Relasi ke Blog
            $table->string('nama');
            $table->string('ttl');
            $table->text('alamat');
            $table->string('sekolah_universitas');
            $table->string('jurusan');
            $table->string('periode_magang');
            $table->text('keahlian');
            $table->json('ketertarikan')->nullable();
            $table->text('ketertarangan_singkat')->nullable();
            $table->string('surat_izin')->nullable();
            $table->string('surat_lamaran')->nullable();
            $table->string('cv_portofolio')->nullable();
            $table->string('foto_diri')->nullable();
            $table->tinyInteger('rating_kreatifitas')->nullable();
            $table->tinyInteger('rating_analitis')->nullable();
            $table->tinyInteger('rating_komunikasi')->nullable();
            $table->tinyInteger('rating_manajemen_waktu')->nullable();
            $table->tinyInteger('rating_adaptasi')->nullable();
            $table->tinyInteger('rating_teamwork')->nullable();
            $table->tinyInteger('rating_motivasi')->nullable();
            $table->tinyInteger('rating_tekanan')->nullable();
            $table->text('alasan_internship')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
