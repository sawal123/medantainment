<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'status',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    protected array $mediaFields = ['logo'];

    protected static function boot()
    {
        parent::boot();

        // [PRIORITAS 11] Cegah race condition penetapan nomor urut baru dengan lockForUpdate
        static::creating(function ($model) {
            if (is_null($model->urutan)) {
                $maxOrder = self::lockForUpdate()->max('urutan');
                $model->urutan = $maxOrder ? $maxOrder + 1 : 1;
            }
        });

        // Saat mengupdate record
        static::updating(function ($model) {
            DB::transaction(function () use ($model) {
                $original = $model->getOriginal('urutan'); // nilai sebelum update
                $new = $model->urutan !== null ? (int) $model->urutan : null;

                // jika tidak ada perubahan urutan atau nilai tidak valid, skip
                if ($new === null || $original === $new) {
                    // jika urutan null set ke akhir (opsional)
                    if ($new === null) {
                        $max = (int) DB::table($model->getTable())->lockForUpdate()->max('urutan');
                        $model->urutan = $max ? $max + 1 : 1;
                    }

                    return;
                }

                // CASE A: new < original -> shift range [new, original-1] +1
                if ($new < $original) {
                    DB::table($model->getTable())
                        ->where('id', '!=', $model->id)
                        ->where('urutan', '>=', $new)
                        ->where('urutan', '<', $original)
                        ->increment('urutan');
                }
                // CASE B: new > original -> shift range [original+1, new] -1
                elseif ($new > $original) {
                    DB::table($model->getTable())
                        ->where('id', '!=', $model->id)
                        ->where('urutan', '<=', $new)
                        ->where('urutan', '>', $original)
                        ->decrement('urutan');
                }
            });
        });

        // Saat menghapus record
        static::deleted(function ($model) {
            DB::transaction(function () use ($model) {
                $deletedPos = (int) $model->urutan;
                DB::table($model->getTable())
                    ->where('urutan', '>', $deletedPos)
                    ->decrement('urutan');
            });
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
