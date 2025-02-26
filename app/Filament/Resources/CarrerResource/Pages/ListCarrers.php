<?php

namespace App\Filament\Resources\CarrerResource\Pages;

use App\Filament\Resources\CarrerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarrers extends ListRecords
{
    protected static string $resource = CarrerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
