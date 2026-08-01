<?php

namespace Tests\Feature\Authorization;

use App\Filament\Resources\AboutUsResource;
use App\Filament\Resources\AlamatResource;
use App\Filament\Resources\BlogResource;
use App\Filament\Resources\BlogResource\Pages\EditBlog;
use App\Filament\Resources\CandidateResource;
use App\Filament\Resources\CarrerResource;
use App\Filament\Resources\CategoryBlogResource;
use App\Filament\Resources\CategoryFilmResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\HeroResource;
use App\Filament\Resources\InternshipResource;
use App\Filament\Resources\LandingResource;
use App\Filament\Resources\PhotoLandingResource;
use App\Filament\Resources\PhotoResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\SlideResource;
use App\Filament\Resources\SosmedResource;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\TeamResource;
use App\Filament\Resources\TestimoniResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
            'role'  => User::ROLE_ADMIN,
        ]);

        $this->author = User::factory()->create([
            'name'  => 'Author User',
            'email' => 'author@example.com',
            'role'  => User::ROLE_AUTHOR,
        ]);
    }

    /**
     * Admin membuka ListUsers dan mendapat sukses.
     */
    public function test_admin_opens_list_users_successfully(): void
    {
        $this->actingAs($this->admin)
            ->get(UserResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertSuccessful();
    }

    /**
     * Author membuka ListUsers dan mendapat forbidden (HTTP 403).
     */
    public function test_author_opens_list_users_is_forbidden(): void
    {
        $this->actingAs($this->author)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        Livewire::actingAs($this->author)
            ->test(ListUsers::class)
            ->assertForbidden();
    }

    /**
     * Author tidak dapat menjalankan EditUser.
     */
    public function test_author_cannot_execute_edit_user(): void
    {
        $this->actingAs($this->author)
            ->get(UserResource::getUrl('edit', ['record' => $this->admin->id]))
            ->assertForbidden();

        Livewire::actingAs($this->author)
            ->test(EditUser::class, ['record' => $this->admin->id])
            ->assertForbidden();
    }

    /**
     * Payload Livewire / request tidak dapat mengubah author menjadi admin.
     */
    public function test_livewire_payload_cannot_change_author_to_admin(): void
    {
        $this->actingAs($this->author);

        try {
            $this->author->update(['role' => User::ROLE_ADMIN]);
            $this->fail('Harusnya melempar ValidationException saat pengguna mengubah role sendiri.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('role', $e->errors());
        }

        $this->assertEquals(User::ROLE_AUTHOR, $this->author->fresh()->role);
    }

    /**
     * Admin tidak dapat mengubah role dirinya sendiri melalui form EditUser atau model update.
     */
    public function test_admin_cannot_change_own_role(): void
    {
        // 1. Coba melalui form EditUser di Livewire (field role disabled & dehydrated(false))
        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $this->admin->id])
            ->fillForm([
                'role' => User::ROLE_AUTHOR,
            ])
            ->call('save');

        $this->assertEquals(User::ROLE_ADMIN, $this->admin->fresh()->role);

        // 2. Coba melalui direct update model saat terautentikasi (server-side boot protection)
        try {
            $this->admin->update(['role' => User::ROLE_AUTHOR]);
            $this->fail('Harusnya melempar ValidationException saat admin mengubah role akun sendiri.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('role', $e->errors());
        }

        $this->assertEquals(User::ROLE_ADMIN, $this->admin->fresh()->role);
    }

    /**
     * Admin terakhir tidak dapat diturunkan perannya.
     */
    public function test_last_admin_cannot_be_demoted(): void
    {
        // Pastikan $this->admin adalah satu-satunya admin
        User::where('role', User::ROLE_ADMIN)
            ->where('id', '!=', $this->admin->id)
            ->delete();

        $anotherUser = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $this->actingAs($anotherUser);

        try {
            $this->admin->update(['role' => User::ROLE_AUTHOR]);
            $this->fail('Harusnya melempar ValidationException saat menurunkan admin terakhir.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('role', $e->errors());
        }

        $this->assertEquals(User::ROLE_ADMIN, $this->admin->fresh()->role);
    }

    /**
     * Admin terakhir tidak dapat dihapus.
     */
    public function test_last_admin_cannot_be_deleted(): void
    {
        // Pastikan $this->admin adalah satu-satunya admin
        User::where('role', User::ROLE_ADMIN)
            ->where('id', '!=', $this->admin->id)
            ->delete();

        try {
            $this->admin->delete();
            $this->fail('Harusnya melempar ValidationException saat menghapus admin terakhir.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('user', $e->errors());
        }

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * UserResource tidak mempunyai bulk delete.
     */
    public function test_user_resource_does_not_have_bulk_delete(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertTableBulkActionDoesNotExist('delete');
    }

    /**
     * Author hanya dapat mengedit blog miliknya.
     */
    public function test_author_can_only_edit_own_blog(): void
    {
        $ownBlog = Blog::factory()->create(['user_id' => $this->author->id]);

        $this->actingAs($this->author)
            ->get(BlogResource::getUrl('edit', ['record' => $ownBlog->id]))
            ->assertSuccessful();

        Livewire::actingAs($this->author)
            ->test(EditBlog::class, ['record' => $ownBlog->id])
            ->assertSuccessful();
    }

    /**
     * Author tidak dapat mengedit blog milik author lain.
     */
    public function test_author_cannot_edit_other_author_blog(): void
    {
        $otherAuthor = User::factory()->create(['role' => User::ROLE_AUTHOR]);
        $otherBlog = Blog::factory()->create(['user_id' => $otherAuthor->id]);

        $response = $this->actingAs($this->author)
            ->get(BlogResource::getUrl('edit', ['record' => $otherBlog->id]));

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404], true));

        try {
            Livewire::actingAs($this->author)
                ->test(EditBlog::class, ['record' => $otherBlog->id]);
            $this->fail('Harusnya menolak/melempar pengecualian saat author mengakses blog author lain.');
        } catch (\Throwable $e) {
            $this->assertTrue(
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Illuminate\View\ViewException
            );
        }
    }

    /**
     * Author mendapat HTTP 403 untuk SETIAP resource non-blog di Filament.
     */
    public function test_author_receives_403_for_all_non_blog_resources(): void
    {
        $nonBlogResources = [
            AboutUsResource::class,
            AlamatResource::class,
            CandidateResource::class,
            CarrerResource::class,
            CategoryBlogResource::class,
            CategoryFilmResource::class,
            ClientResource::class,
            CommentResource::class,
            HeroResource::class,
            InternshipResource::class,
            LandingResource::class,
            PhotoLandingResource::class,
            PhotoResource::class,
            ProjectResource::class,
            SettingResource::class,
            SlideResource::class,
            SosmedResource::class,
            TagResource::class,
            TeamResource::class,
            TestimoniResource::class,
            UserResource::class,
        ];

        foreach ($nonBlogResources as $resource) {
            $this->actingAs($this->author)
                ->get($resource::getUrl('index'))
                ->assertForbidden();
        }
    }
}
