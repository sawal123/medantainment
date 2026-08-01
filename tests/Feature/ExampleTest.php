<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Setting;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Siapkan data dummy settings & alamat agar halaman beranda bisa di-render
        Setting::create([
            'site_name' => 'Medantainment Test',
        ]);

        \App\Models\Alamat::create([
            'street' => 'Jl. Medantainment No. 1',
            'city'   => 'Medan',
            'phone'  => '628123456789',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

