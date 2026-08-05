<?php

namespace Tests\Feature\Project;

use App\Filament\Resources\ProjectResource;
use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectSeries;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectSeriesFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private CategoryFilm $categoryA;

    private CategoryFilm $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'name' => 'Client A',
            'logo' => 'client/logo.png',
            'urutan' => 1,
        ]);

        $this->categoryA = CategoryFilm::create([
            'name' => 'Category A',
            'slug' => 'category-a',
            'deskripsi' => 'Category A description',
            'urutan' => 1,
        ]);

        $this->categoryB = CategoryFilm::create([
            'name' => 'Category B',
            'slug' => 'category-b',
            'deskripsi' => 'Category B description',
            'urutan' => 2,
        ]);
    }

    public function test_standalone_and_episode_identity_preserves_existing_type(): void
    {
        $project = $this->createProject([
            'name' => 'Company Standalone',
            'type' => 'company',
            'category_film_id' => $this->categoryA->id,
            'series_id' => null,
            'urutan' => 1,
        ]);

        $this->assertTrue($project->isStandalone());
        $this->assertSame('company', $project->fresh()->type);

        $series = $this->createSeries('Series A', $this->categoryA, 2);

        $project->update(['series_id' => $series->id]);

        $this->assertTrue($project->fresh()->isEpisode());
        $this->assertSame('company', $project->fresh()->type);
        $this->assertSame($this->categoryA->id, $project->fresh()->category_film_id);

        $project->update(['series_id' => null]);

        $this->assertTrue($project->fresh()->isStandalone());
        $this->assertSame('company', $project->fresh()->type);
    }

    public function test_assignment_service_rejects_forged_category_and_nonexistent_series(): void
    {
        $series = $this->createSeries('Category A Series', $this->categoryA, 1);

        $this->expectException(ValidationException::class);

        ProjectResource::prepareSeriesData([
            'client_id' => $this->client->id,
            'name' => 'Forged Episode',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'company',
            'category_film_id' => $this->categoryB->id,
            'series_id' => $series->id,
            'urutan' => 1,
        ]);
    }

    public function test_project_model_safely_overwrites_episode_category_from_series(): void
    {
        $series = $this->createSeries('Safe Category Series', $this->categoryA, 1);

        $episode = $this->createProject([
            'name' => 'Forged Direct Episode',
            'category_film_id' => $this->categoryB->id,
            'series_id' => $series->id,
            'urutan' => 1,
        ]);

        $this->assertSame($this->categoryA->id, $episode->fresh()->category_film_id);
        $this->assertSame('movie', $episode->fresh()->type);
    }

    public function test_duplicate_episode_order_is_rejected_per_series_but_allowed_across_series(): void
    {
        $seriesA = $this->createSeries('Series A', $this->categoryA, 1);
        $seriesB = $this->createSeries('Series B', $this->categoryB, 2);

        $this->createProject(['name' => 'Episode A1', 'series_id' => $seriesA->id, 'urutan' => 1]);
        $this->createProject(['name' => 'Episode B1', 'series_id' => $seriesB->id, 'urutan' => 1]);

        $this->expectException(QueryException::class);

        $this->createProject(['name' => 'Episode A Duplicate', 'series_id' => $seriesA->id, 'urutan' => 1]);
    }

    public function test_public_project_page_uses_mixed_listing_and_does_not_show_episodes_directly(): void
    {
        $standaloneOne = $this->createProject([
            'name' => 'Standalone One',
            'series_id' => null,
            'urutan' => 1,
        ]);
        $series = $this->createSeries('Series Two', $this->categoryA, 2);
        $standaloneThree = $this->createProject([
            'name' => 'Standalone Three',
            'series_id' => null,
            'urutan' => 3,
        ]);
        $inactiveSeries = ProjectSeries::create([
            'category_film_id' => $this->categoryA->id,
            'name' => 'Inactive Series',
            'slug' => 'inactive-series',
            'urutan' => 4,
            'is_active' => false,
        ]);

        $this->createProject([
            'name' => 'Hidden Episode',
            'series_id' => $series->id,
            'urutan' => 1,
        ]);

        $response = $this->get(route('project.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            $standaloneOne->name,
            $series->name,
            $standaloneThree->name,
        ]);
        $response->assertSee(route('project.series.show', $series->slug), false);
        $response->assertSee('Series / Playlist');
        $response->assertDontSee('Hidden Episode');
        $response->assertDontSee($inactiveSeries->name);
    }

    public function test_public_category_filter_applies_to_projects_and_series_and_unknown_category_404s(): void
    {
        $this->createProject(['name' => 'Category A Project', 'category_film_id' => $this->categoryA->id, 'urutan' => 1]);
        $this->createProject(['name' => 'Category B Project', 'category_film_id' => $this->categoryB->id, 'urutan' => 2]);
        $this->createSeries('Category A Series', $this->categoryA, 3);
        $this->createSeries('Category B Series', $this->categoryB, 4);

        $response = $this->get(route('project.category.show', $this->categoryA->slug));

        $response->assertOk();
        $response->assertSee('Category A Project');
        $response->assertSee('Category A Series');
        $response->assertDontSee('Category B Project');
        $response->assertDontSee('Category B Series');

        $this->get('/project/unknown-category')->assertNotFound();
    }

    public function test_series_route_allows_only_active_series_and_orders_episodes(): void
    {
        $active = $this->createSeries('Active Series', $this->categoryA, 1);
        $inactive = ProjectSeries::create([
            'category_film_id' => $this->categoryA->id,
            'name' => 'Inactive Series',
            'slug' => 'inactive-series',
            'urutan' => 2,
            'is_active' => false,
        ]);

        $this->createProject(['name' => 'Episode Two', 'series_id' => $active->id, 'urutan' => 2]);
        $this->createProject(['name' => 'Episode One', 'series_id' => $active->id, 'urutan' => 1]);

        $response = $this->get(route('project.series.show', $active->slug));

        $response->assertOk();
        $response->assertSeeInOrder(['Episode One', 'Episode Two']);
        $response->assertSee('Episode 1');
        $response->assertSee('Episode 2');

        $this->get(route('project.series.show', $inactive->slug))->assertNotFound();
        $this->get('/project/series/unknown-series')->assertNotFound();
    }

    public function test_series_category_cannot_change_while_it_has_episodes(): void
    {
        $series = $this->createSeries('Locked Series', $this->categoryA, 1);
        $this->createProject(['name' => 'Episode One', 'series_id' => $series->id, 'urutan' => 1]);

        try {
            $series->update(['category_film_id' => $this->categoryB->id]);
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'Kategori series tidak dapat diubah karena masih memiliki episode.',
                $exception->getMessage()
            );
        }

        $this->assertSame($this->categoryA->id, $series->fresh()->category_film_id);

        $emptySeries = $this->createSeries('Editable Series', $this->categoryA, 2);
        $emptySeries->update(['category_film_id' => $this->categoryB->id]);

        $this->assertSame($this->categoryB->id, $emptySeries->fresh()->category_film_id);
    }

    public function test_series_delete_protection_keeps_episode_records_unchanged(): void
    {
        $series = $this->createSeries('Protected Series', $this->categoryA, 1);
        $episode = $this->createProject(['name' => 'Protected Episode', 'series_id' => $series->id, 'urutan' => 1]);

        try {
            $series->delete();
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'Series tidak dapat dihapus karena masih memiliki episode.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('project_series', ['id' => $series->id]);
        $this->assertSame($series->id, $episode->fresh()->series_id);

        $episode->delete();
        $this->assertTrue($series->fresh()->delete());
        $this->assertDatabaseMissing('project_series', ['id' => $series->id]);
    }

    public function test_episode_reorder_swaps_safely_and_other_series_is_untouched(): void
    {
        $seriesA = $this->createSeries('Series A', $this->categoryA, 1);
        $seriesB = $this->createSeries('Series B', $this->categoryB, 2);
        $episodeOne = $this->createProject(['name' => 'Episode One', 'series_id' => $seriesA->id, 'urutan' => 1]);
        $episodeTwo = $this->createProject(['name' => 'Episode Two', 'series_id' => $seriesA->id, 'urutan' => 2]);
        $otherEpisode = $this->createProject(['name' => 'Other Series Episode', 'series_id' => $seriesB->id, 'urutan' => 1]);

        $episodeTwo->moveUp();

        $this->assertSame(2, $episodeOne->fresh()->urutan);
        $this->assertSame(1, $episodeTwo->fresh()->urutan);
        $this->assertSame(1, $otherEpisode->fresh()->urutan);

        $episodeTwo->moveUp();
        $this->assertSame(1, $episodeTwo->fresh()->urutan);

        $episodeTwo->moveDown();
        $this->assertSame(2, $episodeTwo->fresh()->urutan);
        $this->assertSame(1, $episodeOne->fresh()->urutan);

        $episodeTwo->moveDown();
        $this->assertSame(2, $episodeTwo->fresh()->urutan);

        $this->assertSame(
            [1, 2],
            Project::where('series_id', $seriesA->id)->orderBy('urutan')->pluck('urutan')->all()
        );
    }

    private function createSeries(string $name, CategoryFilm $category, int $urutan): ProjectSeries
    {
        return ProjectSeries::create([
            'category_film_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'description' => "{$name} description",
            'thumbnail' => null,
            'urutan' => $urutan,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createProject(array $overrides = []): Project
    {
        $series = isset($overrides['series_id'])
            ? ProjectSeries::find($overrides['series_id'])
            : null;

        return Project::create(array_merge([
            'client_id' => $this->client->id,
            'category_film_id' => $series?->category_film_id ?? $this->categoryA->id,
            'name' => 'Project',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => 'Project description',
            'type' => 'movie',
            'series_id' => null,
            'urutan' => 1,
        ], $overrides));
    }
}
