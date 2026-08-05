<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeNavigationUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('Nilai URL navigasi harus berupa string.');

            return;
        }

        if (static::normalize($value) === null) {
            $fail('URL navigasi tidak aman atau tidak valid. Gunakan path internal (diawali /) atau HTTPS resmi.');
        }
    }

    /**
     * Normalisasi URL navigasi. Mengembalikan URL aman atau null jika tidak valid.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (strlen($value) > 2048) {
            return null;
        }

        // Check raw & URL decoded string for control chars, newline, carriage return, or backslash
        $decoded = rawurldecode($value);
        if (preg_match('/[\r\n\x00-\x1F\x7F\\\\]/', $value) || preg_match('/[\r\n\x00-\x1F\x7F\\\\]/', $decoded)) {
            return null;
        }

        // Rejected scheme prefixes (case-insensitive)
        $dangerousSchemes = ['javascript:', 'data:', 'file:', 'vbscript:', 'ftp:', 'http:'];
        $lowercaseValue = strtolower($value);
        $lowercaseDecoded = strtolower($decoded);

        foreach ($dangerousSchemes as $scheme) {
            if (str_starts_with($lowercaseValue, $scheme) || str_starts_with($lowercaseDecoded, $scheme)) {
                return null;
            }
        }

        // Protocol-relative URLs (e.g., //evil.example, ///evil.example)
        if (str_starts_with($value, '//') || str_starts_with($decoded, '//')) {
            return null;
        }

        // A. Internal URL (starts with a single /)
        if (str_starts_with($value, '/')) {
            if (str_starts_with($value, '//')) {
                return null;
            }

            return $value;
        }

        // B. External URL (must start with https://)
        if (! str_starts_with($lowercaseValue, 'https://')) {
            return null;
        }

        // Parse HTTPS URL
        $parsed = parse_url($value);
        if ($parsed === false || empty($parsed['host'])) {
            return null;
        }

        // Username or password rejection
        if (! empty($parsed['user']) || ! empty($parsed['pass'])) {
            return null;
        }

        return $value;
    }

    /**
     * Periksa apakah URL navigasi valid dan aman.
     */
    public static function isValid(?string $value): bool
    {
        return static::normalize($value) !== null;
    }
}
