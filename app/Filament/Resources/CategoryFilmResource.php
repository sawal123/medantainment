<?php

namespace App\Filament\Resources;

// use Str;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Str;
use App\Models\CategoryFilm;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Resources\CategoryFilmResource\Pages;

class CategoryFilmResource extends Resource
{
    protected static ?string $model = CategoryFilm::class;

    // 🧭 Sidebar settings
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Project'; // tampil di grup Project
    protected static ?string $navigationLabel = 'Category Film';
    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->nullable(),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->image()
                    ->directory('category-films')
                    ->nullable(),


                Forms\Components\TextInput::make('start')
                    ->label('Start (Angka)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

            ])
            ->columns(2);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square()
                    ->defaultImageUrl(url('/default-thumbnail.png')), // opsional

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start')
                    ->label('Start')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
