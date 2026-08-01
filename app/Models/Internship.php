<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use CleansUpMedia, HasFactory;

    protected string $mediaDisk = 'private';

    protected array $mediaFields = [
        'surat_izin',
        'surat_lamaran',
        'cv_portofolio',
        'foto_diri',
    ];

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

    public function carrer()
    {
        return $this->belongsTo(Carrer::class);
    }
}
