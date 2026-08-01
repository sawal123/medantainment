<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait CleansUpMedia
{
    /**
     * Catatan temporary untuk path file lama yang akan dihapus setelah update/delete berhasil.
     *
     * @var array<string>
     */
    protected array $pendingMediaToDelete = [];

    public static function bootCleansUpMedia(): void
    {
        // 1. UPDATE: Catat old path pada updating event
        static::updating(function ($model) {
            $model->pendingMediaToDelete = [];
            foreach ($model->getMediaFields() as $field) {
                if ($model->isDirty($field)) {
                    $oldPath = $model->getOriginal($field);
                    $newPath = $model->{$field};
                    if (! empty($oldPath) && is_string($oldPath) && $oldPath !== $newPath) {
                        $model->pendingMediaToDelete[] = $oldPath;
                    }
                }
            }
        });

        // 2. UPDATE: Hapus file lama pada updated event (karena query UPDATE database sudah berhasil)
        static::updated(function ($model) {
            if (! empty($model->pendingMediaToDelete)) {
                $disk = $model->getMediaDisk();
                $files = $model->pendingMediaToDelete;
                $model->pendingMediaToDelete = [];

                $model->scheduleAfterSuccessfulCommit(function () use ($disk, $files) {
                    foreach ($files as $filePath) {
                        try {
                            if (Storage::disk($disk)->exists($filePath)) {
                                Storage::disk($disk)->delete($filePath);
                            }
                        } catch (\Throwable $e) {
                            $basename = basename($filePath);
                            Log::error("Gagal menghapus file usang [{$basename}] pada disk [{$disk}]: ".$e->getMessage());
                        }
                    }
                });
            }
        });

        // 3. DELETE: Catat file path pada deleting event
        static::deleting(function ($model) {
            // Jika model memakai SoftDeletes dan ini BUKAN force delete, abaikan
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                $model->pendingMediaToDelete = [];

                return;
            }

            $model->pendingMediaToDelete = [];
            foreach ($model->getMediaFields() as $field) {
                $path = $model->{$field};
                if (! empty($path) && is_string($path)) {
                    $model->pendingMediaToDelete[] = $path;
                }
            }
        });

        // 4. DELETE: Hapus file pada deleted / forceDeleted event (karena query DELETE database sudah berhasil)
        $deleteHandler = function ($model) {
            if (! empty($model->pendingMediaToDelete)) {
                $disk = $model->getMediaDisk();
                $files = $model->pendingMediaToDelete;
                $model->pendingMediaToDelete = [];

                $model->scheduleAfterSuccessfulCommit(function () use ($disk, $files) {
                    foreach ($files as $filePath) {
                        try {
                            if (Storage::disk($disk)->exists($filePath)) {
                                Storage::disk($disk)->delete($filePath);
                            }
                        } catch (\Throwable $e) {
                            $basename = basename($filePath);
                            Log::error("Gagal menghapus file record [{$basename}] pada disk [{$disk}]: ".$e->getMessage());
                        }
                    }
                });
            }
        };

        static::deleted($deleteHandler);

        if (method_exists(static::class, 'forceDeleted')) {
            static::forceDeleted($deleteHandler);
        }
    }

    /**
     * Helper internal untuk mengeksekusi callback setelah commit transaksi aktif,
     * atau mengeksekusi langsung jika tidak ada transaksi DB aktif.
     */
    protected function scheduleAfterSuccessfulCommit(callable $callback): void
    {
        $connection = $this->getConnection();

        if ($connection->transactionLevel() > 0) {
            $connection->afterCommit($callback);
        } else {
            $callback();
        }
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
