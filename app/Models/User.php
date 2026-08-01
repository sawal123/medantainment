<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Roles yang diizinkan mengakses panel Filament */
    public const ROLE_ADMIN  = 'admin';
    public const ROLE_AUTHOR = 'author';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected static function boot()
    {
        parent::boot();

        // 1. Proteksi Hapus User (Mencegah Hapus Admin Terakhir & Race Condition)
        static::deleting(function (User $user) {
            if ($user->isAdmin()) {
                DB::transaction(function () use ($user) {
                    // Lock semua baris admin untuk mencegah dua admin saling menghapus secara bersamaan (race condition)
                    $adminCount = static::where('role', self::ROLE_ADMIN)
                        ->lockForUpdate()
                        ->count();

                    if ($adminCount <= 1) {
                        throw ValidationException::withMessages([
                            'user' => 'Admin terakhir tidak dapat dihapus.',
                        ]);
                    }
                });
            }
        });

        // 2. Proteksi Perubahan Role (Mencegah Penurunan Admin Terakhir & Penurunan Role Diri Sendiri)
        static::updating(function (User $user) {
            if ($user->isDirty('role')) {
                $originalRole = $user->getOriginal('role');
                $newRole = $user->role;

                // A. Mencegah user/admin menurunkan/mengubah role akun miliknya sendiri
                if (auth()->check() && (int) auth()->id() === (int) $user->id) {
                    throw ValidationException::withMessages([
                        'role' => 'Anda tidak dapat mengubah peran (role) akun Anda sendiri.',
                    ]);
                }

                // B. Mencegah penurunan role jika ini adalah admin terakhir
                if ($originalRole === self::ROLE_ADMIN && $newRole !== self::ROLE_ADMIN) {
                    DB::transaction(function () use ($user) {
                        $otherAdminsCount = static::where('role', self::ROLE_ADMIN)
                            ->where('id', '!=', $user->id)
                            ->lockForUpdate()
                            ->count();

                        if ($otherAdminsCount === 0) {
                            throw ValidationException::withMessages([
                                'role' => 'Admin terakhir tidak dapat diturunkan perannya.',
                            ]);
                        }
                    });
                }
            }
        });
    }

    /**
     * Hanya admin dan author yang boleh masuk panel Filament.
     * Pengguna dengan role lain (atau tanpa role) ditolak.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AUTHOR], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAuthor(): bool
    {
        return $this->role === self::ROLE_AUTHOR;
    }

    /**
     * Cek apakah user ini adalah satu-satunya admin yang tersisa.
     * Digunakan untuk mencegah penghapusan atau penurunan role admin terakhir.
     */
    public function isLastAdmin(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return static::where('role', self::ROLE_ADMIN)
            ->where('id', '!=', $this->id)
            ->doesntExist();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }
}
