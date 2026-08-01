<?php

namespace Tests\Feature\Security;

use App\Rules\SafeNavigationUrl;
use App\Rules\SafeVideoEmbedUrl;
use Tests\TestCase;

class UrlSecurityTest extends TestCase
{
    public function test_safe_navigation_url_accepts_valid_internal_and_https_external_urls(): void
    {
        $validUrls = [
            '/about-us',
            '/project/test',
            '/page?ref=home',
            'https://example.com',
            'https://example.com/path?q=1',
        ];

        $rule = new SafeNavigationUrl;

        foreach ($validUrls as $url) {
            $failed = false;
            $rule->validate('link', $url, function () use (&$failed) {
                $failed = true;
            });

            $this->assertFalse($failed, "URL valid [{$url}] ditolak oleh SafeNavigationUrl.");
        }
    }

    public function test_safe_navigation_url_rejects_dangerous_schemes_and_malformed_urls(): void
    {
        $invalidUrls = [
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            'file:///etc/passwd',
            '//evil.example',
            'http://example.com',
            'https://user:password@example.com',
            "https://example.com\n/evil",
            'not-a-valid-url-or-path',
        ];

        $rule = new SafeNavigationUrl;

        foreach ($invalidUrls as $url) {
            $failed = false;
            $rule->validate('link', $url, function () use (&$failed) {
                $failed = true;
            });

            $this->assertTrue($failed, "URL tidak aman [{$url}] berhasil lolos validasi SafeNavigationUrl.");
        }
    }

    public function test_safe_video_embed_url_normalizes_youtube_and_vimeo_urls(): void
    {
        $this->assertEquals(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            SafeVideoEmbedUrl::toEmbedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        );

        $this->assertEquals(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            SafeVideoEmbedUrl::toEmbedUrl('https://youtu.be/dQw4w9WgXcQ')
        );

        $this->assertEquals(
            'https://player.vimeo.com/video/123456789',
            SafeVideoEmbedUrl::toEmbedUrl('https://vimeo.com/123456789')
        );
    }

    public function test_hero_video_iframe_renders_mandatory_security_attributes(): void
    {
        $view = $this->blade('
            @if (!empty($hero["hero4"]))
                @php
                    $embedUrl = \App\Rules\SafeVideoEmbedUrl::toEmbedUrl($hero["hero4"]);
                @endphp
                @if (!empty($embedUrl))
                    <iframe src="{{ $embedUrl }}" title="hero-video" frameborder="0"
                        sandbox="allow-scripts allow-same-origin allow-presentation"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen></iframe>
                @endif
            @endif
        ', [
            'hero' => ['hero4' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ]);

        $rendered = (string) $view;

        $this->assertStringContainsString('src="https://www.youtube.com/embed/dQw4w9WgXcQ"', $rendered);
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-presentation"', $rendered);
        $this->assertStringContainsString('loading="lazy"', $rendered);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $rendered);
        $this->assertStringContainsString('allowfullscreen', $rendered);
    }
}
