<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk resource Candidate.
 * Hanya admin yang boleh mengakses data pelamar.
 */
class CandidatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Candidate $candidate): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->isAdmin();
    }
}
