<?php

namespace Tests\Feature\Media;

use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\Client;
use App\Models\Internship;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    private Carrer $carrer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carrer = Carrer::create([
            'title' => 'Software Engineer',
            'description' => 'Job description',
            'time' => 'Full-time',
            'salary' => '10000000',
            'status' => 'open',
        ]);
    }

    public function test_candidate_uses_private_disk(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $path = $file->store('resumes', 'private');

        $candidate = Candidate::create([
            'carrer_id' => $this->carrer->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'resume' => $path,
        ]);

        $this->assertEquals('private', $candidate->getMediaDisk());
        Storage::disk('private')->assertExists($path);
    }

    public function test_internship_uses_private_disk(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');
        $path = $file->store('surat', 'private');

        $intern = Internship::create([
            'carrer_id' => $this->carrer->id,
            'nama' => 'Jane Doe',
            'ttl' => 'Medan, 01-01-2000',
            'alamat' => 'Medan',
            'sekolah_universitas' => 'USU',
            'jurusan' => 'Teknik Informatika',
            'periode_magang' => '3 Bulan',
            'keahlian' => 'PHP',
            'ketertarikan' => ['Web Development'],
            'ketertarangan_singkat' => 'Deskripsi singkat',
            'alasan_internship' => 'Belajar',
            'surat_lamaran' => $path,
        ]);

        $this->assertEquals('private', $intern->getMediaDisk());
        Storage::disk('private')->assertExists($path);
    }

    public function test_public_media_uses_public_disk(): void
    {
        $client = new Client;
        $this->assertEquals('public', $client->getMediaDisk());

        $setting = new Setting;
        $this->assertEquals('public', $setting->getMediaDisk());
    }

    public function test_old_file_remains_when_database_update_fails(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Original Client',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('new_logo.png');
        $newPath = $newFile->store('client', 'public');

        try {
            DB::transaction(function () use ($client, $newPath) {
                $client->update(['logo' => $newPath]);
                throw new \Exception('Database query failure simulation');
            });
        } catch (\Throwable $e) {
            // Expected rollback
        }

        Storage::disk('public')->assertExists($oldPath);
    }

    public function test_old_file_deleted_after_update_succeeds_and_commits(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Original Client',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('new_logo.png');
        $newPath = $newFile->store('client', 'public');

        DB::transaction(function () use ($client, $newPath) {
            $client->update(['logo' => $newPath]);
        });

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_record_file_deleted_after_delete_succeeds_and_commits(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('client', 'public');

        $client = Client::create([
            'name' => 'Client To Delete',
            'logo' => $path,
            'urutan' => 1,
        ]);

        DB::transaction(function () use ($client) {
            $client->delete();
        });

        Storage::disk('public')->assertMissing($path);
    }
}
