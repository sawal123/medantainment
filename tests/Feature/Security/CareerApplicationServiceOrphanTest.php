<?php

namespace Tests\Feature\Security;

use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\Internship;
use App\Services\CareerApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationServiceOrphanTest extends TestCase
{
    use RefreshDatabase;

    private Carrer $carrer;

    private Carrer $internshipCarrer;

    private CareerApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carrer = Carrer::create([
            'title' => 'Backend Engineer',
            'slug' => 'backend-engineer',
            'description' => 'Job description',
            'time' => 'Full-time',
            'salary' => '12000000',
            'status' => 'open',
        ]);

        $this->internshipCarrer = Carrer::create([
            'title' => 'Web Dev Intern',
            'slug' => 'web-dev-intern',
            'description' => 'Internship description',
            'time' => 'Internship',
            'salary' => '3000000',
            'status' => 'open',
        ]);

        $this->service = new CareerApplicationService;
    }

    public function test_candidate_resume_stored_on_success(): void
    {
        Storage::fake('private');

        $resume = UploadedFile::fake()->create('my_resume.pdf', 100, 'application/pdf');

        $candidate = $this->service->submitCandidate($this->carrer, [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '08123456789',
            'cover_letter' => 'Hello',
        ], $resume);

        $this->assertDatabaseHas('candidates', ['id' => $candidate->id, 'email' => 'alice@example.com']);
        Storage::disk('private')->assertExists($candidate->resume);
    }

    public function test_candidate_resume_deleted_and_no_record_created_on_db_failure(): void
    {
        Storage::fake('private');

        $resume = UploadedFile::fake()->create('my_resume.pdf', 100, 'application/pdf');

        // Simulasikan kegagalan database via Candidate model event
        Candidate::creating(function () {
            throw new \Exception('Database constraint error simulation');
        });

        try {
            $this->service->submitCandidate($this->carrer, [
                'name' => 'Bob Failed',
                'email' => 'bob@example.com',
                'phone' => '08123456789',
            ], $resume);
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable $e) {
            $this->assertEquals('Database constraint error simulation', $e->getMessage());
        }

        // Assertion: tidak ada record & tidak ada orphan file di private disk
        $this->assertDatabaseMissing('candidates', ['email' => 'bob@example.com']);
        $allFiles = Storage::disk('private')->allFiles('candidates/resumes');
        $this->assertEmpty($allFiles, 'File orphan resume tidak dibersihkan saat database gagal.');
    }

    public function test_internship_all_files_stored_on_success(): void
    {
        Storage::fake('private');

        $suratIzin = UploadedFile::fake()->create('surat_izin.pdf', 50, 'application/pdf');
        $suratLamaran = UploadedFile::fake()->create('surat_lamaran.pdf', 50, 'application/pdf');

        $internship = $this->service->submitInternship($this->internshipCarrer, [
            'nama' => 'Charlie Intern',
            'ttl' => 'Medan, 01-01-2001',
            'alamat' => 'Medan',
            'sekolah_universitas' => 'USU',
            'jurusan' => 'Teknik Informatika',
            'periode_magang' => '3 Bulan',
            'keahlian' => 'PHP, Laravel',
            'ketertarikan' => ['Web'],
            'ketertarangan_singkat' => 'Singkat',
            'alasan_internship' => 'Belajar',
        ], [
            'surat_izin' => $suratIzin,
            'surat_lamaran' => $suratLamaran,
        ]);

        $this->assertDatabaseHas('internships', ['id' => $internship->id, 'nama' => 'Charlie Intern']);
        Storage::disk('private')->assertExists($internship->surat_izin);
        Storage::disk('private')->assertExists($internship->surat_lamaran);
    }

    public function test_internship_files_cleaned_up_if_db_transaction_fails(): void
    {
        Storage::fake('private');

        $suratIzin = UploadedFile::fake()->create('surat_izin.pdf', 50, 'application/pdf');
        $suratLamaran = UploadedFile::fake()->create('surat_lamaran.pdf', 50, 'application/pdf');

        Internship::creating(function () {
            throw new \Exception('Database failure simulation for Internship');
        });

        try {
            $this->service->submitInternship($this->internshipCarrer, [
                'nama' => 'David Failed',
                'ttl' => 'Medan, 01-01-2001',
                'alamat' => 'Medan',
                'sekolah_universitas' => 'USU',
                'jurusan' => 'Teknik Informatika',
                'periode_magang' => '3 Bulan',
                'keahlian' => 'PHP, Laravel',
                'ketertarikan' => ['Web'],
                'ketertarangan_singkat' => 'Singkat',
                'alasan_internship' => 'Belajar',
            ], [
                'surat_izin' => $suratIzin,
                'surat_lamaran' => $suratLamaran,
            ]);
            $this->fail('Expected exception was not thrown.');
        } catch (\Throwable $e) {
            $this->assertEquals('Database failure simulation for Internship', $e->getMessage());
        }

        $this->assertDatabaseMissing('internships', ['nama' => 'David Failed']);
        $this->assertEmpty(Storage::disk('private')->allFiles('internship/surat_izin'));
        $this->assertEmpty(Storage::disk('private')->allFiles('internship/surat_lamaran'));
    }
}
