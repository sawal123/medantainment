<?php

namespace Tests\Feature\Career;

use App\Livewire\CarrerForm;
use App\Models\Carrer;
use App\Models\User;
use App\Services\CareerSubmissionRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test keamanan CareerForm Livewire.
 */
class CareerFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Career yang sudah closed tidak bisa menerima submit.
     */
    public function test_closed_career_rejects_submission(): void
    {
        $carrer = Carrer::factory()->create([
            'status' => 'closed',
            'time' => 'Full Time',
            'slug' => 'test-career',
        ]);

        $form = new CarrerForm;
        $form->slug = $carrer->slug;
        $form->carrer = $carrer;

        $reflection = new \ReflectionClass($form);
        $method = $reflection->getMethod('getValidCarrer');
        $method->setAccessible(true);

        $result = $method->invoke($form, 'regular');

        $this->assertNull($result);
    }

    /**
     * isLastAdmin() bekerja dengan benar.
     */
    public function test_is_last_admin_returns_true_when_only_admin(): void
    {
        User::truncate();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isLastAdmin());

        User::factory()->create(['role' => 'admin']);

        $this->assertFalse($admin->fresh()->isLastAdmin());
    }

    /**
     * Rate limit key berbeda untuk career berbeda.
     */
    public function test_different_careers_have_different_rate_limit_keys(): void
    {
        $limiter = new CareerSubmissionRateLimiter;

        $key1 = $limiter->buildSessionKey('career', 1, '127.0.0.1', 'sess_1');
        $key2 = $limiter->buildSessionKey('career', 2, '127.0.0.1', 'sess_1');
        $key3 = $limiter->buildSessionKey('internship', 1, '127.0.0.1', 'sess_1');

        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
    }

    /**
     * Tipe form tidak cocok dengan tipe career ditolak.
     */
    public function test_mismatched_form_type_is_rejected(): void
    {
        $regularCareer = Carrer::factory()->create([
            'status' => 'open',
            'time' => 'Full Time',
            'slug' => 'regular-career',
        ]);

        $form = new CarrerForm;
        $form->slug = $regularCareer->slug;
        $form->carrer = $regularCareer;

        $reflection = new \ReflectionClass($form);
        $method = $reflection->getMethod('getValidCarrer');
        $method->setAccessible(true);

        $result = $method->invoke($form, 'Internship');

        $this->assertNull($result);
    }

    /**
     * carrer_id selalu berasal dari $this->carrer->id (server), bukan dari input.
     */
    public function test_carrer_id_comes_from_server_not_user_input(): void
    {
        $carrer = Carrer::factory()->create([
            'status' => 'open',
            'time' => 'Full Time',
            'slug' => 'test-career',
        ]);

        $form = new CarrerForm;
        $form->slug = $carrer->slug;
        $form->carrer = $carrer;

        $form->carrer_id = 9999;

        $reflection = new \ReflectionClass($form);
        $method = $reflection->getMethod('getValidCarrer');
        $method->setAccessible(true);

        $result = $method->invoke($form, 'regular');

        $this->assertNotNull($result);
        $this->assertEquals($carrer->id, $result->id);
        $this->assertNotEquals(9999, $result->id);
    }
}
