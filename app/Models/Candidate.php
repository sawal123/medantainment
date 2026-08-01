<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory;

    /**
     * Field yang diizinkan untuk mass assignment.
     * Tidak termasuk 'status' — status diatur oleh admin, bukan dari form publik.
     */
    protected $fillable = [
        'carrer_id',
        'name',
        'email',
        'phone',
        'resume',
        'cover_letter',
        'status',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Hapus file CV ketika record dihapus permanen
        static::deleted(function (Candidate $candidate) {
            if ($candidate->resume && Storage::disk('private')->exists($candidate->resume)) {
                Storage::disk('private')->delete($candidate->resume);
            }
        });

        // Hapus file lama ketika resume diganti
        static::updating(function (Candidate $candidate) {
            if ($candidate->isDirty('resume') && $candidate->getOriginal('resume')) {
                $oldPath = $candidate->getOriginal('resume');
                if (Storage::disk('private')->exists($oldPath)) {
                    Storage::disk('private')->delete($oldPath);
                }
            }
        });
    }

    public function carrer()
    {
        return $this->belongsTo(\App\Models\Carrer::class);
    }
}
