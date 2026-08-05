<?php

namespace Tests\Feature\Project;

use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectSeriesResource;
use App\Filament\Resources\ProjectSeriesResource\Pages\CreateProjectSeries;
use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectSeries;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectSeriesFilamentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $author;

    private Client $client;

    private CategoryFilm $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->author = User::factory()->create();
        $this->client = Client::create([
            'name' => 'Client A',
            'logo' => 'client/logo.png',
            'urutan' => 1,
        ]);
        $this->category = CategoryFilm::create([
            'name' => 'Category A',
            'slug' => 'category-a',
            'urutan' => 1,
        ]);
    }

    public function test_admin_can_create_series_and_non_admin_cannot_manage_series(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateProjectSeries::class)
            ->fillForm([
                'category_film_id' => $this->category->id,
                'name' => 'Admin Series',
                'slug' => 'admin-series',
                'description' => 'Series description',
                'urutan' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('project_series', [
            'name' => 'Admin Series',
            'category_film_id' => $this->category->id,
        ]);

        $this->actingAs($this->author);
        $this->assertFalse(ProjectSeriesResource::canAccess());
        $this->assertFalse(ProjectSeriesResource::canCreate());
    }

    public function test_admin_can_create_standalone_project_and_episode_without_overwriting_type(): void
    {
        $series = ProjectSeries::create([
            'category_film_id' => $this->category->id,
            'name' => 'Filament Series',
            'slug' => 'filament-series',
            'urutan' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateProject::class)
            ->fillForm([
                'client_id' => $this->client->id,
                'name' => 'Standalone Company',
                'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'Standalone',
                'category_film_id' => $this->category->id,
                'content_kind' => 'standalone',
                'series_id' => null,
                'type' => 'company',
                'urutan' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::actingAs($this->admin)
            ->test(CreateProject::class)
            ->fillForm([
                'client_id' => $this->client->id,
                'name' => 'Episode Company',
                'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'Episode',
                'category_film_id' => $this->category->id,
                'content_kind' => 'episode',
                'series_id' => $series->id,
                'type' => 'company',
                'urutan' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('projects', [
            'name' => 'Standalone Company',
            'series_id' => null,
            'type' => 'company',
        ]);
        $this->assertDatabaseHas('projects', [
            'name' => 'Episode Company',
            'series_id' => $series->id,
            'category_film_id' => $this->category->id,
            'type' => 'company',
        ]);
    }

    public function test_filament_delete_permission_rejects_series_with_episodes(): void
    {
        $series = ProjectSeries::create([
            'category_film_id' => $this->category->id,
            'name' => 'Protected Series',
            'slug' => 'protected-series',
            'urutan' => 1,
            'is_active' => true,
        ]);

        Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->category->id,
            'series_id' => $series->id,
            'name' => 'Protected Episode',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $this->actingAs($this->admin);

        $this->assertFalse(ProjectSeriesResource::canDelete($series->fresh()));

        $series->episodes()->delete();
        $this->assertTrue(ProjectSeriesResource::canDelete($series->fresh()));
    }
}
