<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait CleansUpMedia
{
    public static function bootCleansUpMedia(): void
    {
        // Catat dan hapus file usang SETELAH commit transaksi database berhasil
        static::updating(function ($model) {
            $filesToDelete = [];
            foreach ($model->getMediaFields() as $field) {
                if ($model->isDirty($field)) {
                    $oldPath = $model->getOriginal($field);
                    $newPath = $model->{$field};
                    if (! empty($oldPath) && is_string($oldPath) && $oldPath !== $newPath) {
                        $filesToDelete[] = $oldPath;
                    }
                }
            }

            if (! empty($filesToDelete)) {
                $disk = $model->getMediaDisk();
                DB::afterCommit(function () use ($disk, $filesToDelete) {
                    foreach ($filesToDelete as $filePath) {
                        try {
                            if (Storage::disk($disk)->exists($filePath)) {
                                Storage::disk($disk)->delete($filePath);
                            }
                        } catch (\Throwable $e) {
                            Log::error("Gagal menghapus media usang [{$filePath}] pada disk [{$disk}]: ".$e->getMessage());
                        }
                    }
                });
            }
        });

        // Hapus file SETELAH commit transaksi database berhasil saat record dihapus permanen
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $filesToDelete = [];
            foreach ($model->getMediaFields() as $field) {
                $path = $model->{$field};
                if (! empty($path) && is_string($path)) {
                    $filesToDelete[] = $path;
                }
            }

            if (! empty($filesToDelete)) {
                $disk = $model->getMediaDisk();
                DB::afterCommit(function () use ($disk, $filesToDelete) {
                    foreach ($filesToDelete as $filePath) {
                        try {
                            if (Storage::disk($disk)->exists($filePath)) {
                                Storage::disk($disk)->delete($filePath);
                            }
                        } catch (\Throwable $e) {
                            Log::error("Gagal menghapus media record [{$filePath}] pada disk [{$disk}]: ".$e->getMessage());
                        }
                    }
                });
            }
        });
    }

    /**
     * Field yang diperiksa untuk hapus file.
     */
    public function getMediaFields(): array
    {
        return property_exists($this, 'mediaFields') ? $this->mediaFields : ['image', 'photo', 'logo', 'gambar', 'thumbnail', 'value'];
    }

    /**
     * Disk penyimpanan standar.
     */
    public function getMediaDisk(): string
    {
        return property_exists($this, 'mediaDisk') ? $this->mediaDisk : 'public';
    }
}
