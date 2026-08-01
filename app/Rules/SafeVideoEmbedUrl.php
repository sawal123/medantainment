<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeVideoEmbedUrl implements ValidationRule
{
    /**
     * Jalankan aturan validasi URL video embed.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty(trim($value))) {
            $fail('URL video tidak boleh kosong.');

            return;
        }

        $url = trim($value);

        if (static::toEmbedUrl($url) === '') {
            $fail('URL video tidak valid atau berasal dari domain tidak terpercaya.');
        }
    }

    /**
     * Konversi URL YouTube/Vimeo menjadi URL embed resmi dan aman.
     * Dapat dipanggil langsung tanpa perlu diproses validate() terlebih dahulu.
     */
    public static function toEmbedUrl(?string $url): string
    {
        if ($url === null) {
            return '';
        }

        $url = trim($url);
        if (empty($url) || strlen($url) > 2048) {
            return '';
        }

        // Control character, newline, carriage return, or backslash rejection
        if (preg_match('/[\r\n\x00-\x1F\x7F\\\\]/', $url) || str_contains($url, '<') || str_contains($url, '>')) {
            return '';
        }

        // Protocol-relative URLs or non-HTTPS URLs rejection
        if (! str_starts_with(strtolower($url), 'https://')) {
            return '';
        }

        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['host']) || empty($parsed['host'])) {
            return '';
        }

        // Rejection of credentials or non-default ports
        if (isset($parsed['user']) || isset($parsed['pass']) || isset($parsed['port'])) {
            return '';
        }

        $host = strtolower($parsed['host']);
        $allowedHosts = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'player.vimeo.com',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            return '';
        }

        // YouTube Processing
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be'], true)) {
            $path = $parsed['path'] ?? '';
            $videoId = '';

            if (str_contains($path, '/shorts/')) {
                if (preg_match('/^\/shorts\/([a-zA-Z0-9_-]{5,20})$/', $path, $matches)) {
                    $videoId = $matches[1];
                }
            } elseif ($host === 'youtu.be') {
                $trimmedPath = ltrim($path, '/');
                if (preg_match('/^[a-zA-Z0-9_-]{5,20}$/', $trimmedPath)) {
                    $videoId = $trimmedPath;
                }
            } else {
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $qs);
                    if (! empty($qs['v']) && is_string($qs['v']) && preg_match('/^[a-zA-Z0-9_-]{5,20}$/', $qs['v'])) {
                        $videoId = $qs['v'];
                    }
                }
                if (empty($videoId) && preg_match('/^\/embed\/([a-zA-Z0-9_-]{5,20})$/', $path, $matches)) {
                    $videoId = $matches[1];
                }
            }

            if (! empty($videoId)) {
                return 'https://www.youtube.com/embed/'.$videoId;
            }
        }

        // Vimeo Processing
        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            $path = $parsed['path'] ?? '';
            if (preg_match('/^\/(?:video\/)?(\d{5,15})$/', $path, $matches)) {
                return 'https://player.vimeo.com/video/'.$matches[1];
            }
        }

        return '';
    }
}
