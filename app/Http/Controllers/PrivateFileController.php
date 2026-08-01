<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * PrivateFileController
 *
 * Endpoint download terproteksi untuk dokumen sensitif pelamar.
 * - Hanya admin yang bisa mengunduh.
 * - Path file diambil dari record DB, bukan dari URL langsung.
 * - Mencegah directory traversal.
 * - Mengembalikan 404 jika file tidak ada.
 */
class PrivateFileController extends Controller
{
    /**
     * Field yang diizinkan untuk diunduh dari CandidateResource.
     */
    private const CANDIDATE_ALLOWED_FIELDS = ['resume'];

    /**
     * Field yang diizinkan untuk diunduh dari InternshipResource.
     */
    private const INTERNSHIP_ALLOWED_FIELDS = [
        'surat_izin',
        'surat_lamaran',
        'cv_portofolio',
        'foto_diri',
    ];

    /**
     * Download CV/Resume pelamar (Candidate).
     *
     * Route: GET /secure/candidate/{candidate}/download/{field}
     * Middleware: auth
     */
    public function downloadCandidate(Request $request, Candidate $candidate, string $field)
    {
        // Hanya admin yang boleh mengunduh
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengunduh dokumen pelamar.');
        }

        // Validasi field yang diizinkan — cegah path traversal
        if (! in_array($field, self::CANDIDATE_ALLOWED_FIELDS, true)) {
            abort(404, 'Field tidak ditemukan.');
        }

        $path = $candidate->{$field};

        if (empty($path)) {
            abort(404, 'File tidak tersedia.');
        }

        // Cegah directory traversal: pastikan path berasal dari DB, bukan dari URL
        $path = $this->sanitizePath($path);

        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return Storage::disk('private')->download($path);
    }

    /**
     * Download dokumen peserta magang (Internship).
     *
     * Route: GET /secure/internship/{internship}/download/{field}
     * Middleware: auth
     */
    public function downloadInternship(Request $request, Internship $internship, string $field)
    {
        // Hanya admin yang boleh mengunduh
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengunduh dokumen peserta magang.');
        }

        // Validasi field yang diizinkan — cegah path traversal
        if (! in_array($field, self::INTERNSHIP_ALLOWED_FIELDS, true)) {
            abort(404, 'Field tidak ditemukan.');
        }

        $path = $internship->{$field};

        if (empty($path)) {
            abort(404, 'File tidak tersedia.');
        }

        $path = $this->sanitizePath($path);

        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return Storage::disk('private')->download($path);
    }

    /**
     * Sanitasi path: hilangkan potensi directory traversal.
     * Path sudah berasal dari DB, tapi kita tambah lapisan keamanan ini.
     */
    private function sanitizePath(string $path): string
    {
        // Hilangkan komponen path yang berpotensi traversal
        $path = str_replace(['../', './', '..\\', '.\\'], '', $path);
        $path = ltrim($path, '/\\');

        return $path;
    }
}
