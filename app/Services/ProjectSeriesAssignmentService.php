<?php

namespace App\Services;

use App\Models\ProjectSeries;
use Illuminate\Validation\ValidationException;

class ProjectSeriesAssignmentService
{
    /**
     * Normalize series assignment data without changing the existing project type.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        unset($data['content_kind']);

        if (empty($data['series_id'])) {
            $data['series_id'] = null;

            return $data;
        }

        $series = ProjectSeries::query()->findOrFail($data['series_id']);

        if (
            array_key_exists('category_film_id', $data)
            && filled($data['category_film_id'])
            && (int) $data['category_film_id'] !== (int) $series->category_film_id
        ) {
            throw ValidationException::withMessages([
                'category_film_id' => 'Kategori project harus sama dengan kategori series yang dipilih.',
            ]);
        }

        $data['series_id'] = $series->id;
        $data['category_film_id'] = $series->category_film_id;

        return $data;
    }
}
