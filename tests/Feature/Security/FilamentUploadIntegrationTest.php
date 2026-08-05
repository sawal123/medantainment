<?php

namespace Tests\Feature\Security;

use App\Filament\Resources\BlogResource\Pages\CreateBlog;
use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentUploadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_upload_image_via_filament_sanitizes_dangerous_filenames(): void
    {
        Storage::fake('public');

        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);

        $this->actingAs($author);

        $fakeImage = $this->createFakeDangerousImage('payload.php');

        $test = Livewire::test(CreateBlog::class)
            ->fillForm([
                'title' => 'Blog Security Upload Test',
                'slug' => 'blog-security-upload-test',
                'category_id' => $category->id,
                'content' => 'Blog content test',
                'status' => 'draft',
                'user_id' => $author->id,
                'image' => $fakeImage,
            ])
            ->call('create');

        $test->assertHasNoFormErrors();

        $blog = Blog::where('title', 'Blog Security Upload Test')->first();
        $this->assertNotNull($blog);
        $this->assertEquals($author->id, $blog->user_id);

        $this->assertFalse(str_ends_with(strtolower($blog->image), '.php'));
        $this->assertFalse(str_contains(strtolower(basename($blog->image)), 'payload'));
        $this->assertTrue(
            str_ends_with(strtolower($blog->image), '.png') ||
                str_ends_with(strtolower($blog->image), '.jpg') ||
                str_ends_with(strtolower($blog->image), '.webp')
        );

        Storage::disk('public')->assertExists($blog->image);
    }

    public function test_admin_upload_logo_via_filament_client_resource(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $fakeLogo = $this->createFakeDangerousImage('shell.phtml');

        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Client Integration Test',
                'logo' => $fakeLogo,
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $client = Client::where('name', 'Client Integration Test')->first();
        $this->assertNotNull($client);

        $this->assertFalse(str_ends_with(strtolower($client->logo), '.phtml'));
        $this->assertFalse(str_contains(strtolower(basename($client->logo)), 'shell'));
        Storage::disk('public')->assertExists($client->logo);
    }

    public function test_admin_upload_double_extension_image_jpg_php(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $fakeLogo = $this->createFakeDangerousImage('image.jpg.php');

        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Client Double Ext Test',
                'logo' => $fakeLogo,
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $client = Client::where('name', 'Client Double Ext Test')->first();
        $this->assertNotNull($client);

        $this->assertFalse(str_ends_with(strtolower($client->logo), '.php'));
        $this->assertFalse(str_contains(strtolower(basename($client->logo)), 'image.jpg'));
        Storage::disk('public')->assertExists($client->logo);
    }

    private function createFakeDangerousImage(string $dangerousFilename): UploadedFile
    {
        $file = UploadedFile::fake()
            ->image('safe.png', 100, 100)
            ->mimeType('image/png');

        $file->name = $dangerousFilename;

        return $file;
    }
}
