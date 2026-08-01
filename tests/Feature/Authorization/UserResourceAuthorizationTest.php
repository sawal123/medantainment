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
use App\Services\UserAdministrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ViewException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UserResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $author;

    private UserAdministrationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserAdministrationService::class);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->author = User::factory()->create([
            'name' => 'Author User',
            'email' => 'author@example.com',
            'role' => User::ROLE_AUTHOR,
        ]);
    }

    public function test_admin_opens_list_users_successfully(): void
    {
        $this->actingAs($this->admin)
            ->get(UserResource::getUrl('index'))
            ->assertSuccessful();

        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_author_opens_list_users_is_forbidden(): void
    {
        $this->actingAs($this->author)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();

        Livewire::actingAs($this->author)
            ->test(ListUsers::class)
            ->assertForbidden();
    }

    public function test_author_cannot_execute_edit_user(): void
    {
        $this->actingAs($this->author)
            ->get(UserResource::getUrl('edit', ['record' => $this->admin->id]))
            ->assertForbidden();

        Livewire::actingAs($this->author)
            ->test(EditUser::class, ['record' => $this->admin->id])
            ->assertForbidden();
    }

    public function test_admin_cannot_change_own_role_via_service(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->updateRole($this->admin, $this->admin, User::ROLE_AUTHOR);

        $this->assertEquals(User::ROLE_ADMIN, $this->admin->fresh()->role);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        User::where('role', User::ROLE_ADMIN)
            ->where('id', '!=', $this->admin->id)
            ->delete();

        $this->expectException(ValidationException::class);
        $this->service->updateRole($this->admin, $this->admin, User::ROLE_AUTHOR);
    }

    public function test_last_admin_cannot_be_deleted(): void
    {
        User::where('role', User::ROLE_ADMIN)
            ->where('id', '!=', $this->admin->id)
            ->delete();

        $this->expectException(ValidationException::class);
        $this->service->deleteUser($this->admin, $this->admin);
    }

    public function test_regular_admin_can_demote_another_admin_if_more_admins_remain(): void
    {
        $secondAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $updated = $this->service->updateRole($this->admin, $secondAdmin, User::ROLE_AUTHOR);

        $this->assertEquals(User::ROLE_AUTHOR, $updated->role);
        $this->assertEquals(User::ROLE_AUTHOR, $secondAdmin->fresh()->role);
    }

    public function test_regular_admin_can_delete_another_admin_if_more_admins_remain(): void
    {
        $secondAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $deleted = $this->service->deleteUser($this->admin, $secondAdmin);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);
    }

    public function test_author_cannot_invoke_user_administration_service(): void
    {
        $target = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $this->expectException(AuthorizationException::class);
        $this->service->updateRole($this->author, $target, User::ROLE_ADMIN);
    }

    public function test_user_resource_does_not_have_bulk_delete(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListUsers::class)
            ->assertTableBulkActionDoesNotExist('delete');
    }

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
                $e instanceof ModelNotFoundException ||
                $e instanceof NotFoundHttpException ||
                $e instanceof AuthorizationException ||
                $e instanceof ViewException
            );
        }
    }

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
