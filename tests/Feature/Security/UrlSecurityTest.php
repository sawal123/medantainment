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
            $this->assertNotNull(SafeNavigationUrl::normalize($url));
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
            $this->assertNull(SafeNavigationUrl::normalize($url));
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

    public function test_safe_video_embed_url_rejects_malicious_or_spoofed_video_urls(): void
    {
        $maliciousUrls = [
            'https://youtu.be/dQw4w9WgXcQ/evil',
            'https://youtu.be/dQw4w9WgXcQ?x=<script>',
            'https://youtube.com:444/watch?v=dQw4w9WgXcQ',
            'https://player.vimeo.com.evil.example/video/123456789',
            'https://youtube.com@evil.example/watch?v=dQw4w9WgXcQ',
            'http://www.youtube.com/watch?v=dQw4w9WgXcQ',
            '//youtube.com/embed/dQw4w9WgXcQ',
        ];

        foreach ($maliciousUrls as $url) {
            $this->assertEquals(
                '',
                SafeVideoEmbedUrl::toEmbedUrl($url),
                "URL video tidak aman [{$url}] lolos dari toEmbedUrl()."
            );
        }
    }

    public function test_hero_video_production_partial_renders_mandatory_security_attributes(): void
    {
        $view = $this->view('livewire.partials.hero-video', [
            'hero' => ['hero4' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ]);

        $rendered = (string) $view;

        $this->assertStringContainsString('src="https://www.youtube.com/embed/dQw4w9WgXcQ"', $rendered);
        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-presentation"', $rendered);
        $this->assertStringContainsString('loading="lazy"', $rendered);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $rendered);
        $this->assertStringContainsString('allowfullscreen', $rendered);
        $this->assertStringContainsString('title="hero-video"', $rendered);
    }
}
