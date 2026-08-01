<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeNavigationUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Nilai URL navigasi harus berupa string.');

            return;
        }

        $value = trim($value);

        if ($value === '') {
            return;
        }

        if (strlen($value) > 2048) {
            $fail('Panjang URL navigasi tidak boleh melebihi 2048 karakter.');

            return;
        }

        // Control characters, newline, carriage return, or backslash rejection
        if (preg_match('/[\r\n\x00-\x1F\x7F\\\\]/', $value)) {
            $fail('URL navigasi memuat karakter tidak valid.');

            return;
        }

        // Rejected scheme prefixes (case-insensitive)
        $dangerousSchemes = ['javascript:', 'data:', 'file:', 'vbscript:', 'ftp:', 'http:'];
        $lowercaseValue = strtolower($value);
        foreach ($dangerousSchemes as $scheme) {
            if (str_starts_with($lowercaseValue, $scheme)) {
                $fail('Skema URL tidak diperbolehkan. Gunakan path internal (diawali /) atau HTTPS.');

                return;
            }
        }

        // Protocol-relative URLs (e.g., //evil.example, ///evil.example)
        if (str_starts_with($value, '//')) {
            $fail('URL relatif-protokol (//) tidak diperbolehkan.');

            return;
        }

        // A. Internal URL (starts with a single /)
        if (str_starts_with($value, '/')) {
            return;
        }

        // B. External URL (must start with https://)
        if (! str_starts_with($lowercaseValue, 'https://')) {
            $fail('URL eksternal wajib menggunakan protokol HTTPS (https://).');

            return;
        }

        // Parse HTTPS URL
        $parsed = parse_url($value);
        if ($parsed === false || empty($parsed['host'])) {
            $fail('Struktur URL eksternal tidak valid.');

            return;
        }

        // Username or password rejection
        if (! empty($parsed['user']) || ! empty($parsed['pass'])) {
            $fail('URL eksternal tidak boleh memuat kredensial autentikasi.');

            return;
        }
    }
}
