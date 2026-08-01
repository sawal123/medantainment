<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\Internship;
use App\Support\SafeUploadFilename;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class CareerApplicationService
{
    /**
     * Submit candidate application (regular career).
     * Automatically cleans up newly uploaded resume if persistence fails.
     *
     * @param  TemporaryUploadedFile|UploadedFile  $resume
     */
    public function submitCandidate(Carrer $carrer, array $data, mixed $resume): Candidate
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($carrer, $data, $resume, &$storedFiles) {
                $filename = SafeUploadFilename::forPdf($resume);
                $resumePath = $resume->storeAs('candidates/resumes', $filename, 'private');
                $storedFiles[] = $resumePath;

                return Candidate::create([
                    'carrer_id' => $carrer->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'resume' => $resumePath,
                    'cover_letter' => $data['cover_letter'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            $this->cleanupFiles($storedFiles, 'private', 'Candidate submission');
            throw $e;
        }
    }

    /**
     * Submit internship application.
     * Automatically cleans up newly uploaded files if persistence fails.
     *
     * @param  array<string, TemporaryUploadedFile|UploadedFile|null>  $files
     */
    public function submitInternship(Carrer $carrer, array $data, array $files): Internship
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($carrer, $data, $files, &$storedFiles) {
                $filePaths = [];

                if (! empty($files['surat_izin'])) {
                    $filename = SafeUploadFilename::forPdf($files['surat_izin']);
                    $path = $files['surat_izin']->storeAs('internship/surat_izin', $filename, 'private');
                    $storedFiles[] = $path;
                    $filePaths['surat_izin'] = $path;
                }

                if (! empty($files['surat_lamaran'])) {
                    $filename = SafeUploadFilename::forPdf($files['surat_lamaran']);
                    $path = $files['surat_lamaran']->storeAs('internship/surat_lamaran', $filename, 'private');
                    $storedFiles[] = $path;
                    $filePaths['surat_lamaran'] = $path;
                }

                if (! empty($files['cv_portofolio'])) {
                    $filename = SafeUploadFilename::forPdf($files['cv_portofolio']);
                    $path = $files['cv_portofolio']->storeAs('internship/cv_portofolio', $filename, 'private');
                    $storedFiles[] = $path;
                    $filePaths['cv_portofolio'] = $path;
                }

                if (! empty($files['foto_diri'])) {
                    $filename = SafeUploadFilename::forImage($files['foto_diri']);
                    $path = $files['foto_diri']->storeAs('internship/foto_diri', $filename, 'private');
                    $storedFiles[] = $path;
                    $filePaths['foto_diri'] = $path;
                }

                $payload = array_merge($data, $filePaths, [
                    'carrer_id' => $carrer->id,
                ]);

                return Internship::create($payload);
            });
        } catch (Throwable $e) {
            $this->cleanupFiles($storedFiles, 'private', 'Internship submission');
            throw $e;
        }
    }

    /**
     * Clean up orphaned files safely from storage.
     */
    protected function cleanupFiles(array $paths, string $disk, string $context): void
    {
        foreach ($paths as $filePath) {
            if (empty($filePath) || ! is_string($filePath)) {
                continue;
            }

            try {
                if (Storage::disk($disk)->exists($filePath)) {
                    Storage::disk($disk)->delete($filePath);
                }
            } catch (Throwable $cleanupError) {
                $safeName = basename($filePath);
                Log::error("{$context}: Gagal menghapus orphan file [{$safeName}] pada disk [{$disk}]: ".$cleanupError->getMessage());
            }
        }
    }
}
