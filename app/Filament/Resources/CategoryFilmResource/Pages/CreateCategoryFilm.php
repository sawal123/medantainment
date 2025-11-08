<?php

namespace App\Filament\Resources\CategoryFilmResource\Pages;

use App\Filament\Resources\CategoryFilmResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryFilm extends CreateRecord
{
    protected static string $resource = CategoryFilmResource::class;
}
