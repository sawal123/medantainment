<?php

namespace Tests\Feature\Security;

use App\Livewire\CarrerForm;
use App\Models\Carrer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CareerFormRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    private Carrer $carrer;

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
    }

    public function test_validation_failure_does_not_increment_rate_limiter_attempts(): void
    {
        Storage::fake('private');

        // Form submission with invalid input (missing email and resume)
        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Tester Validation')
            ->call('save')
            ->assertHasErrors(['email', 'resume']);

        // Assert no rate limiting errors exist after validation failure
        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Tester Validation 2')
            ->set('email', 'test2@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors('rate_limit');
    }

    public function test_successful_submission_increments_attempts_and_exceeding_limit_is_rejected(): void
    {
        Storage::fake('private');

        // Submit 3 times successfully (allowed maxAttempts = 3)
        for ($i = 1; $i <= 3; $i++) {
            Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
                ->set('name', "Applicant {$i}")
                ->set('email', "applicant{$i}@example.com")
                ->set('phone', '08123456789')
                ->set('resume', UploadedFile::fake()->create("cv{$i}.pdf", 50, 'application/pdf'))
                ->call('save')
                ->assertHasNoErrors();
        }

        // 4th attempt must be rejected by RateLimiter
        Livewire::test(CarrerForm::class, ['slug' => $this->carrer->slug])
            ->set('name', 'Applicant 4')
            ->set('email', 'applicant4@example.com')
            ->set('phone', '08123456789')
            ->set('resume', UploadedFile::fake()->create('cv4.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('rate_limit');
    }
}
