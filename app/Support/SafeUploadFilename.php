<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SafeUploadFilename
{
    /**
     * Generate a safe random filename for image uploads based solely on server-detected MIME type.
     *
     * @param  TemporaryUploadedFile|UploadedFile|mixed  $file
     */
    public static function forImage(mixed $file): string
    {
        $mimeType = static::detectMimeType($file);

        $extension = match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'file' => 'Format file gambar tidak diizinkan. Hanya JPEG, PNG, dan WebP yang diterima.',
            ]),
        };

        return Str::uuid()->toString().'.'.$extension;
    }

    /**
     * Generate a safe random filename for PDF document uploads based solely on server-detected MIME type.
     *
     * @param  TemporaryUploadedFile|UploadedFile|mixed  $file
     */
    public static function forPdf(mixed $file): string
    {
        $mimeType = static::detectMimeType($file);

        $extension = match ($mimeType) {
            'application/pdf' => 'pdf',
            default => throw ValidationException::withMessages([
                'file' => 'Format file dokumen tidak diizinkan. Hanya berkas PDF yang diterima.',
            ]),
        };

        return Str::uuid()->toString().'.'.$extension;
    }

    /**
     * Extract server-verified MIME type from uploaded file object.
     */
    protected static function detectMimeType(mixed $file): string
    {
        if (is_object($file) && method_exists($file, 'getMimeType')) {
            return (string) $file->getMimeType();
        }

        if ($file instanceof UploadedFile) {
            return (string) $file->getMimeType();
        }

        throw ValidationException::withMessages([
            'file' => 'Tipe berkas tidak dapat diverifikasi oleh server.',
        ]);
    }
}
