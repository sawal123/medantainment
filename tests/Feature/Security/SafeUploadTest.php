<?php

namespace Tests\Feature\Security;

use App\Models\Client;
use App\Support\SafeUploadFilename;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SafeUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_upload_filename_generates_uuid_based_only_on_server_mime(): void
    {
        $pngFile = UploadedFile::fake()->create('payload.php', 10, 'image/png');
        $filename = SafeUploadFilename::forImage($pngFile);

        $this->assertStringEndsWith('.png', $filename);
        $this->assertStringStartsNotWith('payload', $filename);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.png$/i', $filename);
    }

    public function test_safe_upload_filename_for_pdf_stores_as_pdf(): void
    {
        $pdfFile = UploadedFile::fake()->create('malicious.php.pdf', 10, 'application/pdf');
        $filename = SafeUploadFilename::forPdf($pdfFile);

        $this->assertStringEndsWith('.pdf', $filename);
        $this->assertStringStartsNotWith('malicious', $filename);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/i', $filename);
    }

    public function test_dangerous_filenames_are_sanitized_to_safe_extensions_on_blog_resource(): void
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

            $this->assertStringEndsWith('.jpg', $filename);
            $this->assertStringStartsNotWith('.php', $filename);
            $this->assertStringStartsNotWith('.phtml', $filename);
            $this->assertStringStartsNotWith('.phar', $filename);
        }
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

        $this->assertStringEndsWith('.webp', $client->logo);
        $this->assertStringStartsNotWith('exploit', $client->logo);
        Storage::disk('public')->assertExists($client->logo);
    }
}
