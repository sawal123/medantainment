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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
            $table->dateTime('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('author');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Revert status to enum
            $table->enum('status', ['draft', 'published'])->default('draft')->change();
            $table->dropColumn(['published_at', 'seo_title', 'seo_description', 'og_image']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'seo_title', 'seo_description', 'og_image']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role']);
        });
    }
};
