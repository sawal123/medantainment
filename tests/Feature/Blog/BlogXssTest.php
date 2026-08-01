<?php

namespace Tests\Feature\Blog;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogXssTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    /**
     * Uji bahwa tag script dan event handler XSS disanitasi saat menyimpan artikel blog.
     */
    public function test_xss_payloads_in_blog_content_are_sanitized(): void
    {
        $maliciousContent = '<p>Artikel Normal</p><script>alert("XSS")</script><img src="x" onerror="alert(1)"><a href="javascript:alert(2)">Klik di sini</a>';

        $blog = Blog::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Judul Tes XSS',
            'content' => $maliciousContent,
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('<script>', $blog->content);
        $this->assertStringNotContainsString('alert("XSS")', $blog->content);
        $this->assertStringNotContainsString('onerror', $blog->content);
        $this->assertStringNotContainsString('javascript:', $blog->content);

        // Bagian normal harus tetap ada
        $this->assertStringContainsString('<p>Artikel Normal</p>', $blog->content);
    }
}
