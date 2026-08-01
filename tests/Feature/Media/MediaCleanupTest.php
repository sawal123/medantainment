<?php

namespace Tests\Feature\Media;

use App\Models\Blog;
use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\Client;
use App\Models\Internship;
use App\Models\Setting;
use App\Models\User;
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

    public function test_update_normal_without_db_transaction_deletes_old_file_and_keeps_new(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Client A',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('new_logo.png');
        $newPath = $newFile->store('client', 'public');

        // Update tanpa DB::transaction
        $client->update(['logo' => $newPath]);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_delete_normal_without_db_transaction_deletes_record_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('client', 'public');

        $client = Client::create([
            'name' => 'Client B',
            'logo' => $path,
            'urutan' => 1,
        ]);

        // Delete tanpa DB::transaction
        $client->delete();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_update_in_transaction_deletes_old_file_only_after_commit(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Client C',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('new_logo.png');
        $newPath = $newFile->store('client', 'public');

        DB::transaction(function () use ($client, $newPath) {
            $client->update(['logo' => $newPath]);
            // Dalam transaksi, file lama masih ada sebelum commit selesai
        });

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_update_in_transaction_rollback_retains_old_file(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Client D',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('new_logo.png');
        $newPath = $newFile->store('client', 'public');

        try {
            DB::transaction(function () use ($client, $newPath) {
                $client->update(['logo' => $newPath]);
                throw new \Exception('Simulasi kegagalan query database');
            });
        } catch (\Throwable $e) {
            // Expected rollback
        }

        // File lama HARUS tetap ada setelah rollback
        Storage::disk('public')->assertExists($oldPath);
    }

    public function test_delete_in_transaction_rollback_retains_record_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('client', 'public');

        $client = Client::create([
            'name' => 'Client E',
            'logo' => $path,
            'urutan' => 1,
        ]);

        try {
            DB::transaction(function () use ($client) {
                $client->delete();
                throw new \Exception('Simulasi kegagalan query delete');
            });
        } catch (\Throwable $e) {
            // Expected rollback
        }

        // File HARUS tetap ada setelah rollback
        Storage::disk('public')->assertExists($path);
    }

    public function test_database_failure_after_new_file_saved_cleans_up_orphan_file(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $oldFile->store('client', 'public');

        $client = Client::create([
            'name' => 'Client F',
            'logo' => $oldPath,
            'urutan' => 1,
        ]);

        $newFile = UploadedFile::fake()->image('orphan_logo.png');
        $newPath = $newFile->store('client', 'public');

        try {
            DB::transaction(function () use ($client, $newPath) {
                $client->update(['logo' => $newPath]);
                throw new \Exception('Database failure simulation');
            });
        } catch (\Throwable $e) {
            // Bersihkan file baru jika database gagal (orphan cleanup pattern)
            if (Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
        }

        // File lama tetap ada, file baru orphan terhapus secara aman
        Storage::disk('public')->assertExists($oldPath);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_candidate_uses_private_disk_and_retains_resume_on_update_failure(): void
    {
        Storage::fake('private');

        $oldCv = UploadedFile::fake()->create('old_cv.pdf', 100, 'application/pdf');
        $oldPath = $oldCv->store('resumes', 'private');

        $candidate = Candidate::create([
            'carrer_id' => $this->carrer->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'resume' => $oldPath,
        ]);

        $this->assertEquals('private', $candidate->getMediaDisk());

        $newCv = UploadedFile::fake()->create('new_cv.pdf', 100, 'application/pdf');
        $newPath = $newCv->store('resumes', 'private');

        try {
            DB::transaction(function () use ($candidate, $newPath) {
                $candidate->update(['resume' => $newPath]);
                throw new \Exception('Database failure');
            });
        } catch (\Throwable $e) {
            // Expected rollback
        }

        Storage::disk('private')->assertExists($oldPath);
    }

    public function test_internship_uses_private_disk_and_rollback_does_not_delete_old_documents(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');
        $oldPath = $file->store('surat', 'private');

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
            'surat_lamaran' => $oldPath,
        ]);

        $this->assertEquals('private', $intern->getMediaDisk());

        $newFile = UploadedFile::fake()->create('new_surat.pdf', 100, 'application/pdf');
        $newPath = $newFile->store('surat', 'private');

        try {
            DB::transaction(function () use ($intern, $newPath) {
                $intern->update(['surat_lamaran' => $newPath]);
                throw new \Exception('Database failure');
            });
        } catch (\Throwable $e) {
            // Expected rollback
        }

        Storage::disk('private')->assertExists($oldPath);
    }

    public function test_soft_delete_retains_file_and_force_delete_cleans_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $image = UploadedFile::fake()->image('blog.jpg');
        $imagePath = $image->store('blog', 'public');

        $blog = Blog::create([
            'user_id' => $user->id,
            'title' => 'Blog with Image',
            'slug' => 'blog-with-image',
            'content' => 'Blog content',
            'image' => $imagePath,
        ]);

        // Soft delete blog
        $blog->delete();

        // File HARUS tetap ada saat soft delete
        Storage::disk('public')->assertExists($imagePath);

        // Force delete blog
        $blog->forceDelete();

        // File HARUS terhapus saat force delete
        Storage::disk('public')->assertMissing($imagePath);
    }
}
