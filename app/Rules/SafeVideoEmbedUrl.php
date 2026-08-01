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

        // 1. Dilarang mengandung tag HTML (misal iframe)
        if (str_contains($url, '<') || str_contains($url, '>')) {
            $fail('Input tidak boleh mengandung tag HTML atau iframe.');

            return;
        }

        // 2. Wajib HTTPS
        if (! str_starts_with(strtolower($url), 'https://')) {
            $fail('URL video wajib menggunakan protokol HTTPS.');

            return;
        }

        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['host'])) {
            $fail('Format URL video tidak valid.');

            return;
        }

        // 3. Tolak kredensial user/password dalam URL (e.g. user:pass@host)
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            $fail('URL video tidak boleh mengandung kredensial pengguna.');

            return;
        }

        $host = strtolower($parsed['host']);
        $allowedHosts = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'player.vimeo.com',
        ];

        // 4. Host harus persis salah satu dari allowed hosts (mencegah spoofing subdomain)
        if (! in_array($host, $allowedHosts, true)) {
            $fail('URL video harus berasal dari YouTube atau Vimeo resmi.');

            return;
        }

        // 5. Validasi Video ID eksplisit
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be'], true)) {
            $hasVideoId = false;

            if (str_contains($url, '/shorts/')) {
                if (preg_match('/shorts\/([a-zA-Z0-9_-]{5,})/', $url)) {
                    $hasVideoId = true;
                }
            } elseif (preg_match('/(youtu\.be\/|v=|\/embed\/|\/v\/|\/watch\?v=)([a-zA-Z0-9_-]{5,})/', $url)) {
                $hasVideoId = true;
            }

            if (! $hasVideoId) {
                $fail('URL YouTube tidak memiliki ID video yang valid.');

                return;
            }
        } elseif (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            if (! preg_match('/(?:vimeo\.com\/|video\/)(\d+)/', $url)) {
                $fail('URL Vimeo tidak memiliki ID video yang valid.');

                return;
            }
        }
    }

    /**
     * Konversi URL YouTube/Vimeo menjadi URL embed resmi dan aman.
     */
    public static function toEmbedUrl(string $url): string
    {
        $url = trim($url);
        if (empty($url)) {
            return '';
        }

        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['host'])) {
            return '';
        }

        $host = strtolower($parsed['host']);

        // YouTube
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be'], true)) {
            if (str_contains($url, '/shorts/')) {
                if (preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    return 'https://www.youtube.com/embed/'.$matches[1];
                }
            } elseif ($host === 'youtu.be') {
                $path = ltrim($parsed['path'] ?? '', '/');
                if (! empty($path)) {
                    return 'https://www.youtube.com/embed/'.$path;
                }
            } else {
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $qs);
                    if (! empty($qs['v'])) {
                        return 'https://www.youtube.com/embed/'.$qs['v'];
                    }
                }
                if (preg_match('/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    return 'https://www.youtube.com/embed/'.$matches[1];
                }
            }
        }

        // Vimeo
        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('/(?:vimeo\.com\/|video\/)(\d+)/', $url, $matches)) {
                return 'https://player.vimeo.com/video/'.$matches[1];
            }
        }

        return '';
    }
}
