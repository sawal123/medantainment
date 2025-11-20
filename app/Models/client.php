<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class client extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'urutan' => 'integer',
    ];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            if ($project->logo) {
                Storage::disk('public')->delete($project->logo);
            }
        });
        static::creating(function ($model) {
            if (is_null($model->urutan)) {
                $maxOrder = Client::max('urutan');
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
                        $max = (int) DB::table($model->getTable())->max('urutan');
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

                    // model->urutan akan tersimpan sebagai $new
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

    // public static function normalizeOrder()
    // {
    //     DB::transaction(function () {
    //         $table = (new static)->getTable();
    //         $records = DB::table($table)
    //             ->orderBy('urutan')
    //             ->orderBy('id')
    //             ->get(['id', 'urutan']);

    //         $i = 1;
    //         foreach ($records as $r) {
    //             DB::table($table)->where('id', $r->id)->update(['urutan' => $i++]);
    //         }
    //     });
    // }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function photos()
    {
        return $this->hasMany(Project::class);
    }
}
