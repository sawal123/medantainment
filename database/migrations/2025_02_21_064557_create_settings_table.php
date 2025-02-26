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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable(); // Nama situs
            $table->string('logo')->nullable(); // Logo situs
            $table->string('favicon')->nullable(); // Favicon situs
            $table->string('seo_title')->nullable(); // Judul SEO
            $table->text('seo_description')->nullable(); // Deskripsi SEO
            $table->text('seo_keywords')->nullable(); // Kata kunci SEO
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
