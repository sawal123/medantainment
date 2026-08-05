<?php

namespace App\Filament\Resources\ProjectSeriesResource\Pages;

use App\Filament\Resources\ProjectSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectSeries extends ListRecords
{
    protected static string $resource = ProjectSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
