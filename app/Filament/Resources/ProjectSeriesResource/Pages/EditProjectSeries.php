<?php

namespace App\Filament\Resources\ProjectSeriesResource\Pages;

use App\Filament\Resources\ProjectSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectSeries extends EditRecord
{
    protected static string $resource = ProjectSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
