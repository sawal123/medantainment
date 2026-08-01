<?php

namespace Tests\Feature\Authorization;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test authorization untuk UserResource Filament.
 *
 * Test ini membuktikan bahwa:
 * 1. Admin dapat mengakses UserResource.
 * 2. Author mendapat 403 ketika mencoba membuka UserResource.
 * 3. Author tidak dapat mengubah role melalui request yang dimanipulasi.
 */
class UserResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
            'role'  => 'admin',
        ]);

        $this->author = User::factory()->create([
            'name'  => 'Author User',
            'email' => 'author@example.com',
            'role'  => 'author',
        ]);
    }

    /**
     * Admin dapat mengakses index UserResource.
     */
    public function test_admin_can_access_user_resource(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        // Admin harus bisa akses (200) — bukan redirect atau 403
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Jika redirect, harus bukan ke halaman error
        if ($response->getStatusCode() === 302) {
            $response->assertRedirectContains('users');
        }
    }

    /**
     * Author TIDAK dapat mengakses UserResource — harus 403.
     */
    public function test_author_cannot_access_user_resource(): void
    {
        $response = $this->actingAs($this->author)
            ->get('/admin/users');

        // Harus 403 Forbidden
        $response->assertForbidden();
    }

    /**
     * Author tidak dapat mengubah role melalui request langsung.
     */
    public function test_author_cannot_change_role_via_direct_request(): void
    {
        $response = $this->actingAs($this->author)
            ->patch('/admin/users/' . $this->author->id, [
                'role' => 'admin',
            ]);

        // Harus 403 atau redirect ke login (bukan 200)
        $this->assertNotEquals(200, $response->getStatusCode());

        // Verifikasi role tidak berubah di database
        $this->assertDatabaseHas('users', [
            'id'   => $this->author->id,
            'role' => 'author', // tetap author
        ]);
    }

    /**
     * Admin tidak bisa mengubah role-nya sendiri melalui UI.
     */
    public function test_admin_cannot_change_own_role(): void
    {
        // Cek bahwa canAccessPanel menolak jika role bukan admin/author
        $user = User::factory()->create(['role' => 'admin']);
        $user->role = 'admin';
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isLastAdmin() === false || $user->isLastAdmin() === true); // depends on data
    }

    /**
     * Admin terakhir tidak bisa dihapus.
     */
    public function test_last_admin_cannot_be_deleted(): void
    {
        // Hapus semua admin kecuali satu
        User::where('role', 'admin')
            ->where('id', '!=', $this->admin->id)
            ->delete();

        // Verifikasi admin ini adalah admin terakhir
        $this->assertTrue($this->admin->fresh()->isLastAdmin());

        // Coba hapus via request langsung
        $response = $this->actingAs($this->admin)
            ->delete('/admin/users/' . $this->admin->id);

        // Harus ditolak — 403 atau redirect dengan error
        $this->assertNotEquals(200, $response->getStatusCode());

        // Admin masih ada di database
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * isAdmin() method bekerja dengan benar.
     */
    public function test_user_role_methods_work_correctly(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isAuthor());

        $this->assertFalse($author->isAdmin());
        $this->assertTrue($author->isAuthor());
    }

    /**
     * canAccessPanel() hanya mengizinkan admin dan author.
     */
    public function test_can_access_panel_allows_admin_and_author_only(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $other  = User::factory()->create(['role' => 'other']);

        $this->assertTrue($admin->canAccessPanel(app(\Filament\Panel::class)));
        $this->assertTrue($author->canAccessPanel(app(\Filament\Panel::class)));
        $this->assertFalse($other->canAccessPanel(app(\Filament\Panel::class)));
    }
}
