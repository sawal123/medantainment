<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryFilmResource\Pages;
use App\Models\CategoryFilm;
use App\Support\SafeUploadFilename;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Str;

class CategoryFilmResource extends Resource
{
    protected static ?string $model = CategoryFilm::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationLabel = 'Category Film';

    protected static ?int $navigationSort = 1;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'projects as standalone_projects_count' => fn ($query) => $query->whereNull('series_id'),
                'series as series_count',
                'projects as episodes_count' => fn ($query) => $query->whereNotNull('series_id'),
            ]))
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug'),

                TextColumn::make('standalone_projects_count')
                    ->label('Project Biasa')
                    ->sortable(),

                TextColumn::make('series_count')
                    ->label('Series')
                    ->sortable(),

                TextColumn::make('episodes_count')
                    ->label('Episode')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('urutan', 'asc')
            ->actions([
                Tables\Actions\Action::make('up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (CategoryFilm $record) {
                        $above = CategoryFilm::where('urutan', '<', $record->urutan)
                            ->orderBy('urutan', 'desc')
                            ->first();

                        if ($above) {
                            $temp = $record->urutan;
                            $record->update(['urutan' => $above->urutan]);
                            $above->update(['urutan' => $temp]);
                        }
                    }),

                Tables\Actions\Action::make('down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(function (CategoryFilm $record) {
                        $below = CategoryFilm::where('urutan', '>', $record->urutan)
                            ->orderBy('urutan', 'asc')
                            ->first();

                        if ($below) {
                            $temp = $record->urutan;
                            $record->update(['urutan' => $below->urutan]);
                            $below->update(['urutan' => $temp]);
                        }
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi'),

                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->directory('category-films')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string => SafeUploadFilename::forImage($file)
                    ),

                Forms\Components\TextInput::make('start')
                    ->numeric()
                    ->label('Start'),

                Forms\Components\TextInput::make('urutan')
                    ->numeric()
                    ->default(fn () => CategoryFilm::max('urutan') + 1)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif'),
            ])
            ->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryFilms::route('/'),
            'create' => Pages\CreateCategoryFilm::route('/create'),
            'edit' => Pages\EditCategoryFilm::route('/{record}/edit'),
        ];
    }
}
