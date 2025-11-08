<?php

namespace App\Filament\Resources\CategoryFilmResource\Pages;

use App\Filament\Resources\CategoryFilmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryFilms extends ListRecords
{
    protected static string $resource = CategoryFilmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
