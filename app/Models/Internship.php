<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Internship extends Model
{
    use HasFactory;

    /**
     * Field yang diizinkan untuk mass assignment dari form publik.
     * 'status' dikontrol admin, bukan dari form pendaftar.
     * 'carrer_id' di-set dari server ($this->carrer->id), bukan dari input user.
     */
    protected $fillable = [
        'carrer_id',
        'nama',
        'ttl',
        'alamat',
        'sekolah_universitas',
        'jurusan',
        'periode_magang',
        'keahlian',
        'ketertarikan',
        'ketertarangan_singkat',
        'surat_izin',
        'surat_lamaran',
        'cv_portofolio',
        'foto_diri',
        'rating_kreatifitas',
        'rating_analitis',
        'rating_komunikasi',
        'rating_manajemen_waktu',
        'rating_adaptasi',
        'rating_teamwork',
        'rating_motivasi',
        'rating_tekanan',
        'alasan_internship',
        'status',
    ];

    protected $attributes = [
        'status' => 'Pending',
    ];

    protected $casts = [
        'ketertarikan' => 'array',
    ];

    /**
     * Field file yang disimpan di disk private.
     */
    private const FILE_FIELDS = [
        'surat_izin',
        'surat_lamaran',
        'cv_portofolio',
        'foto_diri',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Hapus semua file ketika record dihapus
        static::deleted(function (Internship $internship) {
            foreach (self::FILE_FIELDS as $field) {
                if ($internship->{$field} && Storage::disk('private')->exists($internship->{$field})) {
                    Storage::disk('private')->delete($internship->{$field});
                }
            }
        });

        // Hapus file lama ketika file diganti
        static::updating(function (Internship $internship) {
            foreach (self::FILE_FIELDS as $field) {
                if ($internship->isDirty($field) && $internship->getOriginal($field)) {
                    $oldPath = $internship->getOriginal($field);
                    if (Storage::disk('private')->exists($oldPath)) {
                        Storage::disk('private')->delete($oldPath);
                    }
                }
            }
        });
    }

    public function carrer()
    {
        return $this->belongsTo(\App\Models\Carrer::class);
    }
}
