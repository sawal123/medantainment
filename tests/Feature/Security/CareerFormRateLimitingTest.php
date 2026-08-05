<?php

namespace Tests\Feature\Security;

use App\Livewire\CarrerForm;
use App\Models\Candidate;
use App\Models\Carrer;
use App\Services\CareerApplicationService;
use App\Services\CareerSubmissionRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
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

    public function test_successful_submission_increments_both_buckets_and_commits(): void
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

        $sessionKey = $this->limiter->buildSessionKey('career', 100, '127.0.0.1', 'sess_1');
        $ipKey = $this->limiter->buildIpKey('career', 100, '127.0.0.1');

        $this->assertSame(0, (int) Cache::get($sessionKey, 0));
        $this->assertSame(0, (int) Cache::get($ipKey, 0));
    }

    public function test_different_sessions_same_ip_share_ip_bucket(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $reservation = $this->limiter->reserve(
                'career',
                $this->carrer->id,
                '127.0.0.1',
                "session-{$i}"
            );

            $this->assertNotNull($reservation);
        }

        $blocked = $this->limiter->reserve(
            'career',
            $this->carrer->id,
            '127.0.0.1',
            'session-11'
        );

        $this->assertNull($blocked);
    }

    public function test_different_ips_have_separate_buckets(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', "session-{$i}");
        }

        $blocked = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-11');
        $this->assertNull($blocked);

        $allowed = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.2', 'session-new');
        $this->assertNotNull($allowed);
    }

    public function test_different_careers_have_separate_buckets(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', "session-{$i}");
        }

        $allowed = $this->limiter->reserve('career', $this->internshipCarrer->id, '127.0.0.1', 'session-1');
        $this->assertNotNull($allowed);
    }

    public function test_different_form_types_have_separate_buckets(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', "session-{$i}");
        }

        $allowed = $this->limiter->reserve('internship', $this->carrer->id, '127.0.0.1', 'session-1');
        $this->assertNotNull($allowed);
    }

    public function test_build_lock_key_ignores_session_id_and_shares_by_ip_and_career_and_form(): void
    {
        $lockA = $this->limiter->buildLockKey('career', $this->carrer->id, '127.0.0.1');
        $lockB = $this->limiter->buildLockKey('career', $this->carrer->id, '127.0.0.1');
        $this->assertSame($lockA, $lockB);

        $lockIpDiff = $this->limiter->buildLockKey('career', $this->carrer->id, '127.0.0.2');
        $this->assertNotSame($lockA, $lockIpDiff);

        $lockCareerDiff = $this->limiter->buildLockKey('career', $this->internshipCarrer->id, '127.0.0.1');
        $this->assertNotSame($lockA, $lockCareerDiff);

        $lockFormDiff = $this->limiter->buildLockKey('internship', $this->carrer->id, '127.0.0.1');
        $this->assertNotSame($lockA, $lockFormDiff);
    }

    public function test_rollback_does_not_affect_other_attempts(): void
    {
        $resA = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-a');
        $resB = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-b');

        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');
        $this->assertSame(2, (int) Cache::get($ipKey));

        $this->limiter->rollback($resA);

        $this->assertSame(1, (int) Cache::get($ipKey));
        $this->assertNull(Cache::get($resA['reservation_key']));
        $this->assertNotNull(Cache::get($resB['reservation_key']));

        $this->limiter->commit($resB);
        $this->assertSame(1, (int) Cache::get($ipKey));
        $this->assertNull(Cache::get($resB['reservation_key']));
    }

    public function test_double_rollback_is_idempotent_and_never_causes_negative_or_zero_counter(): void
    {
        $resA = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-a');
        $resB = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-a');

        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');
        $this->assertSame(2, (int) Cache::get($ipKey));

        $this->limiter->rollback($resA);
        $this->assertSame(1, (int) Cache::get($ipKey));

        $this->limiter->rollback($resA);
        $this->assertSame(1, (int) Cache::get($ipKey));
    }

    public function test_commit_prevents_subsequent_rollback(): void
    {
        $resA = $this->limiter->reserve('career', $this->carrer->id, '127.0.0.1', 'session-a');
        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');

        $this->assertSame(1, (int) Cache::get($ipKey));

        $this->limiter->commit($resA);
        $this->assertNull(Cache::get($resA['reservation_key']));

        $this->limiter->rollback($resA);
        $this->assertSame(1, (int) Cache::get($ipKey));
    }

    public function test_database_failure_retains_previous_successful_attempt(): void
    {
        Storage::fake('private');

        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Applicant Success')
            ->set('email', 'success@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv1.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');
        $this->assertSame(1, (int) Cache::get($ipKey));

        Candidate::creating(function () {
            throw new \Exception('DB Failure on second request');
        });

        try {
            Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
                ->set('name', 'Applicant Failed DB')
                ->set('email', 'failed@example.com')
                ->set('phone', '08123456789')
                ->set('resume', UploadedFile::fake()->create('cv2.pdf', 50, 'application/pdf'))
                ->call('save');
        } catch (\Throwable $e) {
            // expected DB exception
        }

        $this->assertSame(1, (int) Cache::get($ipKey));
        $this->assertEquals(1, Candidate::count());
    }

    public function test_filesystem_failure_rolls_back_reservation_without_affecting_previous_success(): void
    {
        Storage::fake('private');

        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Applicant First Success')
            ->set('email', 'first@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv1.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $ipKey = $this->limiter->buildIpKey('career', $this->carrer->id, '127.0.0.1');
        $this->assertSame(1, (int) Cache::get($ipKey));

        $mockService = Mockery::mock(CareerApplicationService::class, function ($mock) {
            $mock->shouldReceive('submitCandidate')
                ->once()
                ->andThrow(new \RuntimeException('Filesystem error simulation'));
        });
        $this->app->instance(CareerApplicationService::class, $mockService);

        try {
            Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
                ->set('name', 'Applicant Filesystem Fail')
                ->set('email', 'fail@example.com')
                ->set('phone', '08123456789')
                ->set('resume', UploadedFile::fake()->create('cv2.pdf', 50, 'application/pdf'))
                ->call('save');
        } catch (\Throwable $e) {
            $this->assertEquals('Filesystem error simulation', $e->getMessage());
        }

        $this->assertSame(1, (int) Cache::get($ipKey));
        $this->assertEquals(1, Candidate::count());
    }

    public function test_cache_lock_mechanism_with_file_cache_driver(): void
    {
        config(['cache.default' => 'file']);

        $resA = $this->limiter->reserve('career', 999, '127.0.0.10', 'session-file-1');
        $this->assertNotNull($resA);
        $this->assertNotEmpty($resA['token']);

        $ipKey = $this->limiter->buildIpKey('career', 999, '127.0.0.10');
        $this->assertSame(1, (int) Cache::get($ipKey));

        $this->limiter->rollback($resA);
        $this->assertSame(0, (int) Cache::get($ipKey, 0));
        $this->assertNull(Cache::get($resA['reservation_key']));
    }
}
