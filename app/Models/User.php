<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Roles yang diizinkan mengakses panel Filament */
    public const ROLE_ADMIN = 'admin';

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

        // Defense-in-depth model events
        static::deleting(function (User $user) {
            if ($user->isAdmin()) {
                $adminCount = static::where('role', self::ROLE_ADMIN)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($adminCount === 0) {
                    throw ValidationException::withMessages([
                        'user' => 'Admin terakhir tidak dapat dihapus.',
                    ]);
                }
            }
        });

        static::updating(function (User $user) {
            if ($user->isDirty('role')) {
                $originalRole = $user->getOriginal('role');
                $newRole = $user->role;

                if (auth()->check() && (int) auth()->id() === (int) $user->id) {
                    throw ValidationException::withMessages([
                        'role' => 'Anda tidak dapat mengubah peran (role) akun Anda sendiri.',
                    ]);
                }

                if ($originalRole === self::ROLE_ADMIN && $newRole !== self::ROLE_ADMIN) {
                    $otherAdminsCount = static::where('role', self::ROLE_ADMIN)
                        ->where('id', '!=', $user->id)
                        ->count();

                    if ($otherAdminsCount === 0) {
                        throw ValidationException::withMessages([
                            'role' => 'Admin terakhir tidak dapat diturunkan perannya.',
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Hanya admin dan author yang boleh masuk panel Filament.
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

    public function isLastAdmin(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return static::where('role', self::ROLE_ADMIN)
            ->where('id', '!=', $this->id)
            ->doesntExist();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }
}
