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
        Schema::create('alamats', function (Blueprint $table) {
            $table->id();
            $table->string('office_name')->default('Kantor Pusat'); // Nama kantor pusat
            $table->string('street'); // Nama jalan
            $table->string('city'); // Kota
            $table->string('state')->nullable(); // Provinsi (opsional)
            $table->string('postal_code')->nullable(); // Kode pos (opsional)
            $table->string('country')->default('Indonesia'); // Negara (default: Indonesia)
            $table->string('phone')->nullable(); // Nomor telepon (opsional)
            $table->string('email')->nullable(); // Email kantor (opsional)
            $table->string('maps_link')->nullable(); // Link Google Maps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamats');
    }
};
