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

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('urutan', 'asc')
            ->actions([

                // 🔼 PINDAH KE ATAS
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

                // 🔽 PINDAH KE BAWAH
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
                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi'),

                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->image()
                    ->directory('category-films'),

                Forms\Components\TextInput::make('start')
                    ->numeric()
                    ->label('Start'),

                Forms\Components\TextInput::make('urutan')
                    ->numeric()
                    ->default(fn() => CategoryFilm::max('urutan') + 1)
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
