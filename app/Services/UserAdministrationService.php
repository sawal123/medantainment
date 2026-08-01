<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAdministrationService
{
    /**
     * Ubah role target user secara atomik dalam satu DB transaction dengan lockForUpdate.
     */
    public function updateRole(User $actor, User $target, string $newRole): User
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Hanya admin yang diizinkan mengelola akun pengguna.');
        }

        if ((int) $actor->id === (int) $target->id && $target->role !== $newRole) {
            throw ValidationException::withMessages([
                'role' => 'Admin tidak dapat mengubah role dirinya sendiri.',
            ]);
        }

        return DB::transaction(function () use ($target, $newRole) {
            /** @var User $lockedTarget */
            $lockedTarget = User::where('id', $target->id)->lockForUpdate()->firstOrFail();

            $oldRole = $lockedTarget->role;

            if ($oldRole === $newRole) {
                return $lockedTarget;
            }

            // Jika menurunkan role admin ke role lain (misal author)
            if ($oldRole === User::ROLE_ADMIN && $newRole !== User::ROLE_ADMIN) {
                $remainingAdminsCount = User::where('role', User::ROLE_ADMIN)
                    ->where('id', '!=', $lockedTarget->id)
                    ->lockForUpdate()
                    ->count();

                if ($remainingAdminsCount === 0) {
                    throw ValidationException::withMessages([
                        'role' => 'Admin terakhir tidak dapat diturunkan perannya.',
                    ]);
                }
            }

            $lockedTarget->role = $newRole;
            $lockedTarget->save();

            return $lockedTarget;
        });
    }

    /**
     * Hapus target user secara atomik dalam satu DB transaction dengan lockForUpdate.
     */
    public function deleteUser(User $actor, User $target): bool
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Hanya admin yang diizinkan mengelola akun pengguna.');
        }

        return DB::transaction(function () use ($target) {
            /** @var User $lockedTarget */
            $lockedTarget = User::where('id', $target->id)->lockForUpdate()->firstOrFail();

            if ($lockedTarget->isAdmin()) {
                $remainingAdminsCount = User::where('role', User::ROLE_ADMIN)
                    ->where('id', '!=', $lockedTarget->id)
                    ->lockForUpdate()
                    ->count();

                if ($remainingAdminsCount === 0) {
                    throw ValidationException::withMessages([
                        'user' => 'Admin terakhir tidak dapat dihapus.',
                    ]);
                }
            }

            return (bool) $lockedTarget->delete();
        });
    }
}
