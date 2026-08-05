<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('series_id')
                ->nullable()
                ->constrained('project_series')
                ->nullOnDelete();
            $table->index('series_id');
            $table->unique(['series_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['series_id', 'urutan']);
            $table->dropConstrainedForeignId('series_id');
        });
    }
};
