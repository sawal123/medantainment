<?php

namespace Tests\Feature\Security;

use App\Models\Client;
use App\Support\SafeUploadFilename;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SafeUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_upload_filename_generates_uuid_based_only_on_server_mime(): void
    {
        $pngFile = UploadedFile::fake()->create('payload.php', 10, 'image/png');
        $filename = SafeUploadFilename::forImage($pngFile);

        $this->assertFalse(str_ends_with(strtolower($filename), '.php'));
        $this->assertFalse(str_contains(strtolower(basename($filename)), 'payload'));
        $this->assertTrue(str_ends_with(strtolower($filename), '.png'));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.png$/i', $filename);
    }

    public function test_safe_upload_filename_for_pdf_stores_as_pdf(): void
    {
        $pdfFile = UploadedFile::fake()->create('malicious.php.pdf', 10, 'application/pdf');
        $filename = SafeUploadFilename::forPdf($pdfFile);

        $this->assertFalse(str_ends_with(strtolower($filename), '.php'));
        $this->assertFalse(str_contains(strtolower(basename($filename)), 'malicious'));
        $this->assertTrue(str_ends_with(strtolower($filename), '.pdf'));
    }

    public function test_dangerous_filenames_are_sanitized_to_safe_extensions(): void
    {
        Storage::fake('public');

        $dangerousNames = [
            'payload.php',
            'shell.phtml',
            'exploit.phar',
            'image.php.jpg',
            'image.jpg.php',
        ];

        foreach ($dangerousNames as $originalName) {
            $file = UploadedFile::fake()->create($originalName, 50, 'image/jpeg');
            $filename = SafeUploadFilename::forImage($file);

            $this->assertFalse(str_ends_with(strtolower($filename), '.php'));
            $this->assertFalse(str_ends_with(strtolower($filename), '.phtml'));
            $this->assertFalse(str_ends_with(strtolower($filename), '.phar'));
            $this->assertTrue(str_ends_with(strtolower($filename), '.jpg'));
            $this->assertFalse(str_contains(strtolower(basename($filename)), 'payload'));
            $this->assertFalse(str_contains(strtolower(basename($filename)), 'shell'));
            $this->assertFalse(str_contains(strtolower(basename($filename)), 'exploit'));
        }
    }

    public function test_plain_text_named_jpg_and_svg_and_gif_are_rejected(): void
    {
        $textFile = UploadedFile::fake()->create('fake_image.jpg', 10, 'text/plain');

        $this->expectException(ValidationException::class);
        SafeUploadFilename::forImage($textFile);
    }

    public function test_svg_image_is_rejected(): void
    {
        $svgFile = UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml');

        $this->expectException(ValidationException::class);
        SafeUploadFilename::forImage($svgFile);
    }

    public function test_admin_resource_client_stores_logo_with_safe_uuid_filename(): void
    {
        Storage::fake('public');

        $fakeFile = UploadedFile::fake()->create('exploit.phtml', 50, 'image/webp');
        $filename = SafeUploadFilename::forImage($fakeFile);

        $storedPath = $fakeFile->storeAs('client', $filename, 'public');

        $client = Client::create([
            'name' => 'Client Security Test',
            'logo' => $storedPath,
            'urutan' => 1,
        ]);

        $this->assertTrue(str_ends_with(strtolower($client->logo), '.webp'));
        $this->assertFalse(str_ends_with(strtolower($client->logo), '.phtml'));
        $this->assertFalse(str_contains(strtolower(basename($client->logo)), 'exploit'));
        Storage::disk('public')->assertExists($client->logo);
    }
}
