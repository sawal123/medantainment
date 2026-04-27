<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Project;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->nullable()->after('type');
        });

        // Set urutan otomatis berdasarkan id yang sudah ada
        $projects = Project::orderBy('id')->get();
        foreach ($projects as $index => $project) {
            $project->timestamps = false;
            $project->updateQuietly(['urutan' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
