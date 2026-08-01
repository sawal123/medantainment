<?php

use App\Rules\SafeNavigationUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clean existing unsafe or malformed slide links in the database.
     */
    public function up(): void
    {
        DB::table('slides')
            ->orderBy('id')
            ->chunkById(100, function ($slides) {
                foreach ($slides as $slide) {
                    if ($slide->link === null || trim((string) $slide->link) === '') {
                        continue;
                    }

                    $normalized = SafeNavigationUrl::normalize((string) $slide->link);

                    if ($normalized !== $slide->link) {
                        DB::table('slides')
                            ->where('id', $slide->id)
                            ->update(['link' => $normalized]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleaning unsafe URLs is a one-way security operation.
    }
};
