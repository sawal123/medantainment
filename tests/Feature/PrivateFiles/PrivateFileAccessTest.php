<?php

namespace Tests\Feature\PrivateFiles;

use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test akses file private.
 *
 * Test ini membuktikan bahwa:
 * 1. Dokumen private tidak dapat diakses tanpa login.
 * 2. Author tidak dapat mengunduh dokumen pelamar.
 * 3. Admin dapat mengunduh dokumen pelamar.
 * 4. Path traversal ditolak.
 */
class PrivateFileAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $author;
    private Candidate $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->admin  = User::factory()->create(['role' => 'admin']);
        $this->author = User::factory()->create(['role' => 'author']);

        $carrer = Carrer::factory()->create([
            'status' => 'open',
            'time'   => 'Full Time',
        ]);

        // Buat file palsu di disk private
        Storage::disk('private')->put('candidates/resumes/test-cv.pdf', '%PDF-1.4 fake content');

        $this->candidate = Candidate::factory()->create([
            'carrer_id' => $carrer->id,
            'resume'    => 'candidates/resumes/test-cv.pdf',
        ]);
    }

    /**
     * Guest tidak bisa akses endpoint download — harus redirect ke login.
     */
    public function test_guest_cannot_download_private_file(): void
    {
        $response = $this->get(route('secure.candidate.download', [
            'candidate' => $this->candidate->id,
            'field'     => 'resume',
        ]));

        // Harus redirect ke halaman login (302) — bukan 200
        $response->assertRedirectContains('login');
    }

    /**
     * Author tidak bisa download CV pelamar — harus 403.
     */
    public function test_author_cannot_download_candidate_file(): void
    {
        $response = $this->actingAs($this->author)
            ->get(route('secure.candidate.download', [
                'candidate' => $this->candidate->id,
                'field'     => 'resume',
            ]));

        $response->assertForbidden();
    }

    /**
     * Admin bisa download CV pelamar.
     */
    public function test_admin_can_download_candidate_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('secure.candidate.download', [
                'candidate' => $this->candidate->id,
                'field'     => 'resume',
            ]));

        // Harus berhasil (200) atau download (200 dengan Content-Disposition)
        $response->assertSuccessful();
    }

    /**
     * Path traversal melalui field name ditolak.
     */
    public function test_path_traversal_via_field_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('secure.candidate.download', [
                'candidate' => $this->candidate->id,
                'field'     => '../../../etc/passwd',
            ]));

        // Harus 404 karena field tidak ada di allowlist
        $response->assertNotFound();
    }

    /**
     * Field yang tidak ada dalam allowlist ditolak.
     */
    public function test_non_whitelisted_field_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('secure.candidate.download', [
                'candidate' => $this->candidate->id,
                'field'     => 'email', // email bukan file field
            ]));

        $response->assertNotFound();
    }

    /**
     * File yang tidak ada mengembalikan 404.
     */
    public function test_missing_file_returns_404(): void
    {
        // Hapus file dari disk
        Storage::disk('private')->delete('candidates/resumes/test-cv.pdf');

        $response = $this->actingAs($this->admin)
            ->get(route('secure.candidate.download', [
                'candidate' => $this->candidate->id,
                'field'     => 'resume',
            ]));

        $response->assertNotFound();
    }
}
