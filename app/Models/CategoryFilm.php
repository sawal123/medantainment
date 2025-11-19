<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryFilm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'thumbnail',
        'deskripsi',
        'start',
        'slug',
        'urutan',
        'is_active',
    ];

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Jika tidak isi urutan, otomatis set ke urutan terakhir
            if (is_null($model->urutan)) {
                $model->urutan = CategoryFilm::max('urutan') + 1;
            }
        });

        static::updating(function ($model) {
            // Jika user mengubah urutan, lakukan re-shuffle
            if ($model->isDirty('urutan')) {
                $oldOrder = $model->getOriginal('urutan');
                $newOrder = $model->urutan;

                if ($newOrder < $oldOrder) {
                    CategoryFilm::whereBetween('urutan', [$newOrder, $oldOrder - 1])
                        ->increment('urutan');
                } else {
                    CategoryFilm::whereBetween('urutan', [$oldOrder + 1, $newOrder])
                        ->decrement('urutan');
                }
            }
        });

        static::deleting(function ($model) {
            // Jika dihapus, rapikan ulang
            CategoryFilm::where('urutan', '>', $model->urutan)
                ->decrement('urutan');
        });
    }
}
