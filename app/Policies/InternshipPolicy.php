<?php

namespace App\Policies;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk resource Internship.
 * Hanya admin yang boleh mengakses data peserta magang.
 */
class InternshipPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Internship $internship): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Internship $internship): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Internship $internship): bool
    {
        return $user->isAdmin();
    }
}
