<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Hanya admin yang boleh melihat daftar user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh melihat detail user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh membuat user baru.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh mengedit user.
     * Admin tidak boleh mengedit role-nya sendiri menjadi bukan admin
     * jika ia satu-satunya admin.
     */
    public function update(User $user, User $model): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return true; // validasi perubahan role dilakukan di Resource
    }

    /**
     * Hanya admin yang boleh menghapus user.
     * Admin tidak boleh menghapus dirinya sendiri jika itu admin terakhir.
     */
    public function delete(User $user, User $model): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        // Cegah penghapusan admin terakhir
        if ($model->isAdmin() && $model->isLastAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Hanya admin yang boleh restore user.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang boleh force delete user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
