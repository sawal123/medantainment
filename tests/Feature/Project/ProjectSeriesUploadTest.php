<?php

namespace Tests\Feature\Project;

use App\Filament\Resources\ProjectSeriesResource\Pages\CreateProjectSeries;
use App\Filament\Resources\ProjectSeriesResource\Pages\EditProjectSeries;
use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectSeries;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectSeriesUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private CategoryFilm $categoryA;

    private CategoryFilm $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->categoryA = CategoryFilm::create([
            'name' => 'Category A',
            'slug' => 'category-a',
            'urutan' => 1,
        ]);
        $this->categoryB = CategoryFilm::create([
            'name' => 'Category B',
            'slug' => 'category-b',
            'urutan' => 2,
        ]);
    }

    public function test_series_thumbnail_dangerous_filename_is_sanitized(): void
    {
        Storage::fake('public');

        $file = $this->dangerousImage('series.php');

        Livewire::actingAs($this->admin)
            ->test(CreateProjectSeries::class)
            ->fillForm([
                'category_film_id' => $this->categoryA->id,
                'name' => 'Upload Series',
                'slug' => 'upload-series',
                'description' => 'Upload series',
                'thumbnail' => $file,
                'urutan' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $series = ProjectSeries::where('slug', 'upload-series')->firstOrFail();

        $this->assertFalse(str_contains(strtolower(basename($series->thumbnail)), 'series'));
        $this->assertFalse(str_ends_with(strtolower($series->thumbnail), '.php'));
        $this->assertMatchesRegularExpression('/\.(jpg|jpeg|png|webp)$/', strtolower($series->thumbnail));
        Storage::disk('public')->assertExists($series->thumbnail);
    }

    public function test_replacing_thumbnail_deletes_old_file_after_successful_update(): void
    {
        Storage::fake('public');

        $oldPath = UploadedFile::fake()->image('old.png')->store('project-series/thumbnails', 'public');
        $newPath = UploadedFile::fake()->image('new.png')->store('project-series/thumbnails', 'public');
        $series = ProjectSeries::create([
            'category_film_id' => $this->categoryA->id,
            'name' => 'Replace Series',
            'slug' => 'replace-series',
            'thumbnail' => $oldPath,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $series->update(['thumbnail' => $newPath]);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_failed_update_removes_new_orphan_thumbnail_and_keeps_old_file(): void
    {
        Storage::fake('public');

        $client = Client::create([
            'name' => 'Client A',
            'logo' => 'client/logo.png',
            'urutan' => 1,
        ]);
        $oldPath = UploadedFile::fake()->image('old.png')->store('project-series/thumbnails', 'public');
        $series = ProjectSeries::create([
            'category_film_id' => $this->categoryA->id,
            'name' => 'Failed Update Series',
            'slug' => 'failed-update-series',
            'thumbnail' => $oldPath,
            'urutan' => 1,
            'is_active' => true,
        ]);
        Project::create([
            'client_id' => $client->id,
            'category_film_id' => $this->categoryA->id,
            'series_id' => $series->id,
            'name' => 'Episode',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditProjectSeries::class, ['record' => $series->getRouteKey()])
            ->fillForm([
                'category_film_id' => $this->categoryB->id,
                'name' => 'Failed Update Series',
                'slug' => 'failed-update-series',
                'description' => null,
                'thumbnail' => $this->dangerousImage('new.php'),
                'urutan' => 1,
                'is_active' => true,
            ])
            ->call('save');

        Storage::disk('public')->assertExists($oldPath);
        $this->assertSame($oldPath, $series->fresh()->thumbnail);
        $this->assertCount(1, Storage::disk('public')->files('project-series/thumbnails'));
    }

    public function test_deleting_empty_series_removes_thumbnail(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('series.png')->store('project-series/thumbnails', 'public');
        $series = ProjectSeries::create([
            'category_film_id' => $this->categoryA->id,
            'name' => 'Delete Series',
            'slug' => 'delete-series',
            'thumbnail' => $path,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $series->delete();

        Storage::disk('public')->assertMissing($path);
    }

    private function dangerousImage(string $dangerousFilename): UploadedFile
    {
        $file = UploadedFile::fake()
            ->image('safe.png', 100, 100)
            ->mimeType('image/png');

        $file->name = $dangerousFilename;

        return $file;
    }
}
