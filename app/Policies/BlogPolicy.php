<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk resource Blog.
 * Hanya admin atau pemilik blog (author) yang boleh melakukan operasi.
 */
class BlogPolicy
{
    use HandlesAuthorization;

    /**
     * Admin & author yang sudah punya akses panel boleh melihat daftar blog.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAuthor();
    }

    /**
     * Admin boleh melihat semua. Author hanya boleh melihat miliknya.
     */
    public function view(User $user, Blog $blog): bool
    {
        return $user->isAdmin() || (int) $blog->user_id === (int) $user->id;
    }

    /**
     * Admin dan author boleh membuat artikel baru.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAuthor();
    }

    /**
     * Admin boleh edit semua. Author hanya boleh edit miliknya.
     */
    public function update(User $user, Blog $blog): bool
    {
        return $user->isAdmin() || (int) $blog->user_id === (int) $user->id;
    }

    /**
     * Admin boleh hapus semua. Author hanya boleh hapus miliknya.
     */
    public function delete(User $user, Blog $blog): bool
    {
        return $user->isAdmin() || (int) $blog->user_id === (int) $user->id;
    }

    /**
     * Hanya admin yang boleh restore blog yang dihapus.
     */
    public function restore(User $user, Blog $blog): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh force delete blog.
     */
    public function forceDelete(User $user, Blog $blog): bool
    {
        return $user->isAdmin();
    }
}
