<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait CleansUpMedia
{
    public static function bootCleansUpMedia(): void
    {
        // Bersihkan file lama jika field media diganti dengan file baru
        static::updating(function ($model) {
            foreach ($model->getMediaFields() as $field) {
                if ($model->isDirty($field) && ($oldPath = $model->getOriginal($field))) {
                    if (Storage::disk($model->getMediaDisk())->exists($oldPath)) {
                        Storage::disk($model->getMediaDisk())->delete($oldPath);
                    }
                }
            }
        });

        // Bersihkan file jika record dihapus
        static::deleting(function ($model) {
            foreach ($model->getMediaFields() as $field) {
                if (($path = $model->{$field}) && is_string($path)) {
                    if (Storage::disk($model->getMediaDisk())->exists($path)) {
                        Storage::disk($model->getMediaDisk())->delete($path);
                    }
                }
            }
        });
    }

    /**
     * Field yang diperiksa untuk hapus file.
     * Override parameter $mediaFields pada model jika nama field berbeda.
     */
    protected function getMediaFields(): array
    {
        return property_exists($this, 'mediaFields') ? $this->mediaFields : ['image', 'photo', 'logo', 'gambar', 'thumbnail', 'value'];
    }

    /**
     * Disk penyimpanan standar.
     * Override $mediaDisk pada model jika menggunakan disk berbeda.
     */
    protected function getMediaDisk(): string
    {
        return property_exists($this, 'mediaDisk') ? $this->mediaDisk : 'public';
    }
}
