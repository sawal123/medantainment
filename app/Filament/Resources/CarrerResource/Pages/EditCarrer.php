<?php

namespace App\Filament\Resources\CarrerResource\Pages;

use App\Filament\Resources\CarrerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarrer extends EditRecord
{
    protected static string $resource = CarrerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
