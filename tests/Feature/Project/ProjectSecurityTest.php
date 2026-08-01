<?php

namespace Tests\Feature\Project;

use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Project;
use App\Rules\SafeVideoEmbedUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProjectSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private CategoryFilm $categoryFilm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'name' => 'Test Client',
            'logo' => 'client/dummy.png',
            'urutan' => 1,
        ]);

        $this->categoryFilm = CategoryFilm::create([
            'name' => 'Movie',
            'slug' => 'movie',
            'urutan' => 1,
        ]);
    }

    public function test_move_up_swaps_order_with_previous_record(): void
    {
        $p1 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 1',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $p2 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 2',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 2,
        ]);

        $p2->moveUp();

        $this->assertEquals(1, $p2->fresh()->urutan);
        $this->assertEquals(2, $p1->fresh()->urutan);
    }

    public function test_move_down_swaps_order_with_next_record(): void
    {
        $p1 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 1',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $p2 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 2',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 2,
        ]);

        $p1->moveDown();

        $this->assertEquals(2, $p1->fresh()->urutan);
        $this->assertEquals(1, $p2->fresh()->urutan);
    }

    public function test_move_up_first_record_does_not_change_order(): void
    {
        $p1 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 1',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $p1->moveUp();

        $this->assertEquals(1, $p1->fresh()->urutan);
    }

    public function test_move_down_last_record_does_not_change_order(): void
    {
        $p1 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 1',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $p1->moveDown();

        $this->assertEquals(1, $p1->fresh()->urutan);
    }

    public function test_valid_embed_urls_pass_validation(): void
    {
        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://vimeo.com/123456789',
            'https://player.vimeo.com/video/123456789',
        ];

        foreach ($validUrls as $url) {
            $validator = Validator::make(['link' => $url], ['link' => [new SafeVideoEmbedUrl]]);
            $this->assertFalse($validator->fails(), "URL valid ditolak: {$url}");
        }
    }

    public function test_invalid_embed_urls_fail_validation(): void
    {
        $invalidUrls = [
            'http://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            'https://youtube.com.attacker.example/watch?v=12345',
            'https://attacker.youtube.com/watch?v=12345',
            'https://user:pass@youtube.com/watch?v=12345',
            '/relative/path/video',
            'not_a_url',
            'https://youtube.com/watch?v=',
            'https://unknown-domain.com/video.mp4',
            '<iframe src="https://youtube.com"></iframe>',
        ];

        foreach ($invalidUrls as $url) {
            $validator = Validator::make(['link' => $url], ['link' => [new SafeVideoEmbedUrl]]);
            $this->assertTrue($validator->fails(), "URL tidak valid lolos: {$url}");
        }
    }

    public function test_iframe_views_contain_required_security_attributes(): void
    {
        $p1 = Project::create([
            'client_id' => $this->client->id,
            'category_film_id' => $this->categoryFilm->id,
            'name' => 'Project 1',
            'link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'type' => 'movie',
            'urutan' => 1,
        ]);

        $projectHtml = view('livewire.project', [
            'selectedCategory' => 'all',
            'categoryFilm' => collect(),
            'firstCategory' => null,
            'films' => collect([$p1]),
            'filmLimit' => 10,
            'totalFilms' => 1,
        ])->render();

        $this->assertStringContainsString('loading="lazy"', $projectHtml);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $projectHtml);
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"', $projectHtml);
        $this->assertStringContainsString('allowfullscreen', $projectHtml);
    }
}
