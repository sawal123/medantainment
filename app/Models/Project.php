<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'link',
        'description',
        'start_date',
        'end_date',
        'category_film_id',
        'type',
        'urutan',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function categoryFilm()
    {
        return $this->belongsTo(CategoryFilm::class);
    }

    public function setLinkAttribute($value)
    {
        $this->attributes['link'] = $this->convertToEmbed($value);
    }

    /**
     * Pindah ke atas (urutan berkurang) — menggunakan transaction + lockForUpdate.
     */
    public function moveUp(): void
    {
        DB::transaction(function () {
            /** @var Project|null $previous */
            $previous = static::lockForUpdate()
                ->where('urutan', '<', $this->urutan)
                ->orderBy('urutan', 'desc')
                ->first();

            if ($previous) {
                $oldUrutan = $this->urutan;
                $this->updateQuietly(['urutan' => $previous->urutan]);
                $previous->updateQuietly(['urutan' => $oldUrutan]);
            }
        });
    }

    /**
     * Pindah ke bawah (urutan bertambah) — menggunakan transaction + lockForUpdate.
     */
    public function moveDown(): void
    {
        DB::transaction(function () {
            /** @var Project|null $next */
            $next = static::lockForUpdate()
                ->where('urutan', '>', $this->urutan)
                ->orderBy('urutan', 'asc')
                ->first();

            if ($next) {
                $oldUrutan = $this->urutan;
                $this->updateQuietly(['urutan' => $next->urutan]);
                $next->updateQuietly(['urutan' => $oldUrutan]);
            }
        });
    }

    /**
     * Normalisasi URL YouTube ke format embed yang aman.
     * Hanya menerima HTTPS dari domain YouTube atau Vimeo.
     * Menolak javascript:, data:, URL relatif, dan domain lain.
     *
     * @throws \InvalidArgumentException jika URL tidak valid
     */
    private function convertToEmbed($url): string
    {
        if (empty($url)) {
            return '';
        }

        // Hanya terima HTTPS
        if (! str_starts_with($url, 'https://')) {
            throw new \InvalidArgumentException(
                'URL video harus menggunakan HTTPS dan berasal dari YouTube atau Vimeo.'
            );
        }

        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');

        $allowedHosts = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'player.vimeo.com',
            'vimeo.com',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            throw new \InvalidArgumentException(
                'URL video harus berasal dari YouTube atau Vimeo.'
            );
        }

        // YouTube Shorts
        if (str_contains($url, '/shorts/')) {
            preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches);
            if (isset($matches[1])) {
                return "https://www.youtube.com/embed/{$matches[1]}";
            }
        }

        // YouTube normal
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be'], true)) {
            preg_match('/(youtu\.be\/|v=|\/embed\/|\/v\/|\/watch\?v=)([a-zA-Z0-9_-]+)/', $url, $matches);
            if (isset($matches[2])) {
                return "https://www.youtube.com/embed/{$matches[2]}";
            }
        }

        // Vimeo — hanya terima format embed
        if (in_array($host, ['player.vimeo.com', 'vimeo.com'], true)) {
            preg_match('/(?:vimeo\.com\/|video\/)(\d+)/', $url, $matches);
            if (isset($matches[1])) {
                return "https://player.vimeo.com/video/{$matches[1]}";
            }
        }

        throw new \InvalidArgumentException(
            'Format URL video tidak dikenali. Pastikan URL YouTube atau Vimeo valid.'
        );
    }
}
