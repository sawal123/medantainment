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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();              // Judul utama
            $table->text('subtitle')->nullable();             // Subjudul atau tagline
            $table->longText('description')->nullable();      // Deskripsi panjang
            $table->string('vision')->nullable();             // Visi
            $table->string('mission_title')->nullable();      // Judul misi
            $table->longText('mission')->nullable();          // Misi (bisa paragraf)
            $table->string('image')->nullable();              // Foto utama
            $table->json('highlights')->nullable();           // Poin-poin keunggulan
            $table->string('video_url')->nullable();          // YouTube / Vimeo embed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
