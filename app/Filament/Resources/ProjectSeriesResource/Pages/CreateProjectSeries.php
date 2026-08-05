<?php

namespace App\Filament\Resources\ProjectSeriesResource\Pages;

use App\Filament\Resources\ProjectSeriesResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateProjectSeries extends CreateRecord
{
    protected static string $resource = ProjectSeriesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (\Throwable $e) {
            if (! empty($data['thumbnail'])) {
                Storage::disk('public')->delete($data['thumbnail']);
            }

            throw $e;
        }
    }
}
