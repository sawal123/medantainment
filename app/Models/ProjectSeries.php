<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_film_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'urutan',
        'is_active',
    ];

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
}
