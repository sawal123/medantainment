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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrer_id')->constrained()->onDelete('cascade'); // Relasi ke Blog
            $table->string('name'); // Nama pelamar
            $table->string('email')->unique(); // Email pelamar (unik)
            $table->string('phone'); // Nomor telepon
            $table->string('resume'); // Path file CV yang diupload
            $table->text('cover_letter')->nullable(); // Surat lamaran (opsional)
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending'); // Status seleksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
