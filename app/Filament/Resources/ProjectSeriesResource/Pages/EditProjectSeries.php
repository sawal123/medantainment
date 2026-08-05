<?php

namespace App\Filament\Resources\ProjectSeriesResource\Pages;

use App\Filament\Resources\ProjectSeriesResource;
use App\Models\ProjectSeries;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditProjectSeries extends EditRecord
{
    protected static string $resource = ProjectSeriesResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldThumbnail = $record->thumbnail;
        $newThumbnail = $data['thumbnail'] ?? null;

        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (\Throwable $e) {
            if ($newThumbnail && $newThumbnail !== $oldThumbnail) {
                Storage::disk('public')->delete($newThumbnail);
            }

            throw $e;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(fn (ProjectSeries $record) => $record->episodes()->exists())
                ->modalDescription('Series tidak dapat dihapus karena masih memiliki episode.'),
        ];
    }
}
