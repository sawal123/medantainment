<?php

namespace Tests\Feature\Security;

use App\Livewire\CarrerForm;
use App\Models\Candidate;
use App\Models\Carrer;
use App\Services\CareerSubmissionRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CareerFormRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    private Carrer $carrer;

    private Carrer $internshipCarrer;

    private CareerSubmissionRateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carrer = Carrer::create([
            'title' => 'Software Developer',
            'slug' => 'software-developer',
            'description' => 'Job description',
            'time' => 'Full-time',
            'salary' => '10000000',
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

        $this->limiter = new CareerSubmissionRateLimiter;
    }

    public function test_validation_failure_does_not_increment_attempts(): void
    {
        Storage::fake('private');

        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Tester Validation')
            ->call('save')
            ->assertHasErrors(['email', 'resume']);

        $sessionKey = $this->limiter->buildSessionKey('career', $this->carrer->id, '127.0.0.1', session()->getId());
        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');

        $this->assertEquals(0, Cache::get($sessionKey, 0));
        $this->assertEquals(0, Cache::get($ipKey, 0));
    }

    public function test_successful_submission_increments_both_buckets(): void
    {
        Storage::fake('private');

        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Applicant 1')
            ->set('email', 'applicant1@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv1.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $sessionKey = $this->limiter->buildSessionKey('career', $this->carrer->id, '127.0.0.1', session()->getId());
        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');

        $this->assertEquals(1, Cache::get($sessionKey));
        $this->assertEquals(1, Cache::get($ipKey));
    }

    public function test_database_failure_rolls_back_reservation(): void
    {
        Storage::fake('private');

        Candidate::creating(function () {
            throw new \Exception('DB Exception Simulation');
        });

        try {
            Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
                ->set('name', 'Applicant Failed')
                ->set('email', 'failed@example.com')
                ->set('phone', '08123456789')
                ->set('resume', UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'))
                ->call('save');
        } catch (\Throwable $e) {
            $this->assertEquals('DB Exception Simulation', $e->getMessage());
        }

        $sessionKey = $this->limiter->buildSessionKey('career', $this->carrer->id, '127.0.0.1', session()->getId());
        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');

        $this->assertEquals(0, Cache::get($sessionKey, 0));
        $this->assertEquals(0, Cache::get($ipKey, 0));
    }

    public function test_fourth_request_in_session_limit_is_rejected(): void
    {
        Storage::fake('private');

        for ($i = 1; $i <= 3; $i++) {
            Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
                ->set('name', "Applicant {$i}")
                ->set('email', "applicant{$i}@example.com")
                ->set('phone', '08123456789')
                ->set('resume', UploadedFile::fake()->create("cv{$i}.pdf", 50, 'application/pdf'))
                ->call('save')
                ->assertHasNoErrors();
        }

        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Applicant 4')
            ->set('email', 'applicant4@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv4.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('rate_limit');
    }

    public function test_rollback_never_causes_negative_counter(): void
    {
        $res = $this->limiter->reserve('career', 100, '127.0.0.1', 'sess_1');
        $this->assertNotNull($res);

        $this->limiter->rollback($res);
        $this->limiter->rollback($res); // Double rollback attempt

        $sessionKey = $res['sessionKey'];
        $ipKey = $res['ipKey'];

        $this->assertGreaterThanOrEqual(0, (int) Cache::get($sessionKey, 0));
        $this->assertGreaterThanOrEqual(0, (int) Cache::get($ipKey, 0));
    }
}
