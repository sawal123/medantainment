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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke Blog
            $table->string('title')->unique(); // Judul Blog
            $table->string('slug')->unique(); // Slug untuk SEO
            $table->text('content'); // Isi Konten
            $table->string('image')->nullable(); // Gambar Utama
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Kategori Blog
            $table->enum('status', ['draft', 'published'])->default('draft'); // Status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
