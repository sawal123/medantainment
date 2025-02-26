<?php

namespace App\Filament\Resources\PhotoLandingResource\Pages;

use App\Filament\Resources\PhotoLandingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotoLandings extends ListRecords
{
    protected static string $resource = PhotoLandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
