<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;

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
