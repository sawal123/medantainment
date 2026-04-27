<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function categoryFilm()
    {
        return $this->belongsTo(\App\Models\CategoryFilm::class);
    }

    public function setLinkAttribute($value)
    {
        $this->attributes['link'] = $this->convertToEmbed($value);
    }

    /**
     * Pindah ke atas (urutan berkurang)
     */
    public function moveUp(): void
    {
        $previous = static::where('urutan', '<', $this->urutan)
            ->orderBy('urutan', 'desc')
            ->first();

        if ($previous) {
            $oldUrutan = $this->urutan;
            $this->updateQuietly(['urutan' => $previous->urutan]);
            $previous->updateQuietly(['urutan' => $oldUrutan]);
        }
    }

    /**
     * Pindah ke bawah (urutan bertambah)
     */
    public function moveDown(): void
    {
        $next = static::where('urutan', '>', $this->urutan)
            ->orderBy('urutan', 'asc')
            ->first();

        if ($next) {
            $oldUrutan = $this->urutan;
            $this->updateQuietly(['urutan' => $next->urutan]);
            $next->updateQuietly(['urutan' => $oldUrutan]);
        }
    }

    private function convertToEmbed($url)
    {
        // Cek apakah link YouTube
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {

            // Pattern untuk YouTube Shorts
            if (strpos($url, '/shorts/') !== false) {
                preg_match('/shorts\/([^?]+)/', $url, $matches);

                return isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : $url;
            }

            // Pattern untuk YouTube normal
            preg_match('/(youtu\.be\/|v=|\/embed\/|\/v\/|\/watch\?v=)([^&]+)/', $url, $matches);

            return isset($matches[2]) ? "https://www.youtube.com/embed/{$matches[2]}" : $url;
        }

        return $url;
    }
}
