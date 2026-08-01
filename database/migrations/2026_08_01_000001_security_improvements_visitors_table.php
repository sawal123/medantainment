<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Security improvements untuk tabel visitors.
 *
 * - Tambah kolom ip_hash (hash SHA256 dari IP + app key) — tidak simpan IP mentah
 * - Tambah kolom is_bot (flag bot detection)
 * - Tambah index untuk query statistik
 * - Composite index untuk dedup check (session_id + blog_id + created_at)
 *
 * Aman dijalankan pada database yang sudah berisi data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Tambah kolom ip_hash jika belum ada
            if (! Schema::hasColumn('visitors', 'ip_hash')) {
                $table->string('ip_hash', 64)->nullable()->after('ip_address')
                    ->comment('SHA256(ip + app_key) — tidak menyimpan IP asli');
            }

            // Tambah kolom is_bot jika belum ada
            if (! Schema::hasColumn('visitors', 'is_bot')) {
                $table->boolean('is_bot')->default(false)->after('user_agent');
            }

            // Index untuk query statistik per blog
            if (! $this->indexExists('visitors', 'visitors_blog_id_index')) {
                $table->index('blog_id', 'visitors_blog_id_index');
            }

            // Index untuk query statistik per tanggal
            if (! $this->indexExists('visitors', 'visitors_created_at_index')) {
                $table->index('created_at', 'visitors_created_at_index');
            }

            // Composite index untuk dedup check
            if (! $this->indexExists('visitors', 'visitors_dedup_index')) {
                $table->index(['session_id', 'blog_id', 'created_at'], 'visitors_dedup_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Hapus index
            $table->dropIndex('visitors_blog_id_index');
            $table->dropIndex('visitors_created_at_index');
            $table->dropIndex('visitors_dedup_index');

            // Hapus kolom
            if (Schema::hasColumn('visitors', 'ip_hash')) {
                $table->dropColumn('ip_hash');
            }
            if (Schema::hasColumn('visitors', 'is_bot')) {
                $table->dropColumn('is_bot');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\Illuminate\Support\Facades\Schema::getIndexes($table))
            ->pluck('name')
            ->contains($indexName);
    }
};
