<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProjectSeries extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'category_film_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'urutan',
        'is_active',
    ];

    protected array $mediaFields = ['thumbnail'];

    protected string $mediaDisk = 'public';

    public function categoryFilm()
    {
        return $this->belongsTo(CategoryFilm::class);
    }

    public function episodes()
    {
        return $this->hasMany(Project::class, 'series_id')
            ->orderBy('urutan');
    }

    public function getEpisodeCountAttribute(): int
    {
        return $this->episodes()->count();
    }

    protected static function booted(): void
    {
        static::updating(function (ProjectSeries $series) {
            // Saat kategori playlist/series diubah, semua episode (Film) di dalamnya
            // ikut menyesuaikan kategori agar tetap konsisten dengan series-nya.
            // update() pada query builder langsung menulis DB tanpa memicu event model.
            if ($series->isDirty('category_film_id')) {
                Project::query()
                    ->where('series_id', $series->getKey())
                    ->update(['category_film_id' => $series->category_film_id]);
            }
        });

        static::deleting(function (ProjectSeries $series) {
            if ($series->episodes()->exists()) {
                throw ValidationException::withMessages([
                    'series' => 'Series tidak dapat dihapus karena masih memiliki episode.',
                ]);
            }
        });
    }
}
