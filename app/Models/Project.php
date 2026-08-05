<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'series_id',
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

    public function series()
    {
        return $this->belongsTo(ProjectSeries::class, 'series_id');
    }

    public function isEpisode(): bool
    {
        return $this->series_id !== null;
    }

    public function isStandalone(): bool
    {
        return $this->series_id === null;
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if ($project->series_id === null) {
                return;
            }

            $series = ProjectSeries::query()->findOrFail($project->series_id);
            $project->category_film_id = $series->category_film_id;
        });
    }

    public function setLinkAttribute($value)
    {
        $this->attributes['link'] = $this->convertToEmbed($value);
    }

    public function moveUp(): void
    {
        DB::transaction(function () {
            $current = $this->freshForReorder();
            $previous = $this->reorderScope()
                ->where('urutan', '<', $this->urutan)
                ->orderBy('urutan', 'desc')
                ->first();

            if ($previous) {
                $this->swapOrderWith($current, $previous);
            }
        });

        $this->refresh();
    }

    public function moveDown(): void
    {
        DB::transaction(function () {
            $current = $this->freshForReorder();
            $next = $this->reorderScope()
                ->where('urutan', '>', $this->urutan)
                ->orderBy('urutan', 'asc')
                ->first();

            if ($next) {
                $this->swapOrderWith($current, $next);
            }
        });

        $this->refresh();
    }

    private function freshForReorder(): self
    {
        $current = static::query()
            ->lockForUpdate()
            ->findOrFail($this->getKey());

        $this->series_id = $current->series_id;
        $this->urutan = $current->urutan;

        return $current;
    }

    private function reorderScope(): Builder
    {
        return static::query()
            ->lockForUpdate()
            ->where('series_id', $this->series_id);
    }

    private function swapOrderWith(Project $current, Project $neighbor): void
    {
        $currentOrder = $current->urutan;
        $neighborOrder = $neighbor->urutan;
        $temporaryOrder = $this->reorderScope()->max('urutan') + 1;

        $current->updateQuietly(['urutan' => $temporaryOrder]);
        $neighbor->updateQuietly(['urutan' => $currentOrder]);
        $current->updateQuietly(['urutan' => $neighborOrder]);
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

        if (str_contains($url, '/shorts/')) {
            preg_match('/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches);
            if (isset($matches[1])) {
                return "https://www.youtube.com/embed/{$matches[1]}";
            }
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be'], true)) {
            preg_match('/(youtu\.be\/|v=|\/embed\/|\/v\/|\/watch\?v=)([a-zA-Z0-9_-]+)/', $url, $matches);
            if (isset($matches[2])) {
                return "https://www.youtube.com/embed/{$matches[2]}";
            }
        }

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
