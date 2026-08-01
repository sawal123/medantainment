<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAdministrationService
{
    /**
     * Pemutakhiran lengkap data user (role, name, email, password, dll) secara atomik dalam 1 DB transaction.
     */
    public function updateUser(User $actor, User $target, array $data): User
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Hanya admin yang diizinkan mengelola akun pengguna.');
        }

        return DB::transaction(function () use ($actor, $target, $data) {
            /** @var User $lockedTarget */
            $lockedTarget = User::where('id', $target->id)->lockForUpdate()->firstOrFail();

            if (isset($data['role']) && $data['role'] !== $lockedTarget->role) {
                $newRole = $data['role'];

                // Self role change check
                if ((int) $actor->id === (int) $lockedTarget->id) {
                    throw ValidationException::withMessages([
                        'role' => 'Admin tidak dapat mengubah role dirinya sendiri.',
                    ]);
                }

                // Last admin check if demoting
                if ($lockedTarget->role === User::ROLE_ADMIN && $newRole !== User::ROLE_ADMIN) {
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
            }

            $lockedTarget->update($data);

            return $lockedTarget;
        });
    }

    /**
     * Ubah role target user secara atomik dalam satu DB transaction dengan lockForUpdate.
     */
    public function updateRole(User $actor, User $target, string $newRole): User
    {
        return $this->updateUser($actor, $target, ['role' => $newRole]);
    }

    /**
     * Hapus target user secara atomik dalam satu DB transaction dengan lockForUpdate.
     */
    public function deleteUser(User $actor, User $target): bool
    {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Hanya admin yang diizinkan mengelola akun pengguna.');
        }

        return DB::transaction(function () use ($actor, $target) {
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
