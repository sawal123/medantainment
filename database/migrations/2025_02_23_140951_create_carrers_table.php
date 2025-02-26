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
        Schema::create('carrers', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul posisi kerja
            $table->text('description'); // Deskripsi pekerjaan
            $table->text('requirements'); // Persyaratan pekerjaan
            $table->enum('status', ['open', 'closed'])->default('open'); // Status lowongan
            $table->string('apply_link')->nullable(); // URL form pendaftaran (opsional)
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrers');
    }
};
