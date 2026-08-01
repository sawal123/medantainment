<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah index pada kolom 'urutan' untuk Client dan Project.
 *
 * Ini meningkatkan performa query ordering dan mencegah race condition
 * karena lockForUpdate() bekerja lebih efisien dengan index.
 *
 * Aman dijalankan pada database yang sudah berisi data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Index untuk ordering yang efisien
            $table->index('urutan', 'clients_urutan_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('urutan', 'projects_urutan_index');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_urutan_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_urutan_index');
        });
    }
};
