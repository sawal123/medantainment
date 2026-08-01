<?php

namespace Tests\Feature\Authorization;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test authorization untuk BlogResource Filament.
 *
 * Test ini membuktikan bahwa:
 * 1. Author hanya dapat melihat dan mengedit blog miliknya sendiri.
 * 2. Author tidak dapat mengedit blog milik user lain.
 */
class BlogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $author;
    private User $otherAuthor;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->author = User::factory()->create(['role' => 'author']);
        $this->otherAuthor = User::factory()->create(['role' => 'author']);

        $this->category = Category::factory()->create();
    }

    /**
     * BlogPolicy: author hanya bisa update blog miliknya.
     */
    public function test_author_can_update_own_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->author->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        // Author bisa update blog miliknya
        $this->assertTrue(
            $this->author->can('update', $blog)
        );
    }

    /**
     * BlogPolicy: author tidak bisa update blog milik author lain.
     */
    public function test_author_cannot_update_other_users_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->otherAuthor->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        // Author tidak bisa update blog milik user lain
        $this->assertFalse(
            $this->author->can('update', $blog)
        );
    }

    /**
     * BlogPolicy: admin bisa update semua blog.
     */
    public function test_admin_can_update_any_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->author->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        $this->assertTrue(
            $this->admin->can('update', $blog)
        );
    }

    /**
     * BlogPolicy: author tidak bisa delete blog milik user lain.
     */
    public function test_author_cannot_delete_other_users_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->otherAuthor->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        $this->assertFalse(
            $this->author->can('delete', $blog)
        );
    }

    /**
     * BlogPolicy: author bisa delete blog miliknya sendiri.
     */
    public function test_author_can_delete_own_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->author->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        $this->assertTrue(
            $this->author->can('delete', $blog)
        );
    }

    /**
     * BlogPolicy: hanya admin yang bisa force delete.
     */
    public function test_only_admin_can_force_delete_blog(): void
    {
        $blog = Blog::factory()->create([
            'user_id'     => $this->author->id,
            'category_id' => $this->category->id,
            'status'      => 'draft',
        ]);

        $this->assertTrue($this->admin->can('forceDelete', $blog));
        $this->assertFalse($this->author->can('forceDelete', $blog));
    }
}
