<?php

namespace App\Filament\Resources\ProjectSeriesResource\Pages;

use App\Filament\Resources\ProjectSeriesResource;
use App\Models\ProjectSeries;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EditProjectSeries extends EditRecord
{
    protected static string $resource = ProjectSeriesResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        } catch (ValidationException $exception) {
            // Surface model-level validation (e.g. category locked while the
            // series still has episodes) as a form error + notification
            // instead of letting it bubble up as a raw exception.
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError('data.'.$field, $message);
                }
            }

            Notification::make()
                ->danger()
                ->title('Gagal menyimpan')
                ->body($exception->getMessage())
                ->send();
        }
    }

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
