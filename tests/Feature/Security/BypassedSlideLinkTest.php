<?php

namespace Tests\Feature\Security;

use App\Models\Slide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BypassedSlideLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_dangerous_slide_links_bypassed_in_db_return_null_via_safe_link_accessor(): void
    {
        $dangerousLinks = [
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            '//evil.example',
            'http://evil.example',
            'https://user:pass@evil.example',
            "https://example.com\n/evil",
        ];

        foreach ($dangerousLinks as $dangerousLink) {
            $id = DB::table('slides')->insertGetId([
                'nama' => 'Bypassed Slide',
                'thumbnail' => 'slides/test.jpg',
                'link' => $dangerousLink,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $slide = Slide::find($id);

            $this->assertNull(
                $slide->safe_link,
                "Link berbahaya [{$dangerousLink}] yang lolos di database harus menghasilkan null pada safe_link."
            );
        }
    }

    public function test_bypassed_dangerous_link_is_not_rendered_as_active_anchor_in_home_view(): void
    {
        $id = DB::table('slides')->insertGetId([
            'nama' => 'Malicious Slide',
            'thumbnail' => 'slides/test.jpg',
            'link' => 'javascript:alert(1)',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $slide = Slide::find($id);

        $rendered = (string) $this->view('livewire.home', [
            'slide' => collect([$slide]),
            'hero' => [],
            'client' => collect([]),
            'showAll' => true,
            'categoryFilm' => collect([]),
            'photo' => [],
            'testimoni' => collect([]),
            'blog' => collect([]),
        ]);

        $this->assertStringNotContainsString('javascript:alert(1)', $rendered);
        $this->assertStringNotContainsString('<a href="javascript', $rendered);
    }

    public function test_valid_internal_and_external_slide_links_render_correctly(): void
    {
        $internalSlide = Slide::create([
            'nama' => 'Internal Slide',
            'thumbnail' => 'slides/int.jpg',
            'link' => '/about-us',
            'is_active' => true,
        ]);

        $externalSlide = Slide::create([
            'nama' => 'External Slide',
            'thumbnail' => 'slides/ext.jpg',
            'link' => 'https://example.com/project',
            'is_active' => true,
        ]);

        $this->assertEquals('/about-us', $internalSlide->safe_link);
        $this->assertEquals('https://example.com/project', $externalSlide->safe_link);

        $rendered = (string) $this->view('livewire.home', [
            'slide' => collect([$internalSlide, $externalSlide]),
            'hero' => [],
            'client' => collect([]),
            'showAll' => true,
            'categoryFilm' => collect([]),
            'photo' => [],
            'testimoni' => collect([]),
            'blog' => collect([]),
        ]);

        $this->assertStringContainsString('href="/about-us"', $rendered);
        $this->assertStringContainsString('href="https://example.com/project"', $rendered);
        $this->assertStringContainsString('target="_blank"', $rendered);
        $this->assertStringContainsString('rel="noopener noreferrer"', $rendered);
    }
}
