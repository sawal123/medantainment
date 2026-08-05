<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectSeriesResource\Pages;
use App\Models\CategoryFilm;
use App\Models\ProjectSeries;
use App\Support\SafeUploadFilename;
use Illuminate\Support\Str;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectSeriesResource extends Resource
{
    protected static ?string $model = ProjectSeries::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Series / Playlist';

    protected static ?string $navigationGroup = 'Project';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('category_film_id')
                            ->label('Kategori Film')
                            ->relationship('categoryFilm', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Nama Series')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail Series')
                            ->disk('public')
                            ->directory('project-series/thumbnails')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->getUploadedFileNameForStorageUsing(fn($file): string => SafeUploadFilename::forImage($file)),

                        TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(fn() => ProjectSeries::max('urutan') + 1)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan', 'asc')
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable(),
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nama Series')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('categoryFilm.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable()
                    ->limit(15),
                TextColumn::make('episodes_count')
                    ->label('Jumlah Episode')
                    ->counts('episodes'),
                BadgeColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('category_film_id')
                    ->label('Kategori Film')
                    ->options(fn() => CategoryFilm::orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('Semua Kategori'),
                SelectFilter::make('is_active')
                    ->label('Status Aktif')
                    ->options([1 => 'Aktif', 0 => 'Non-aktif']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn(ProjectSeries $record) => $record->episodes()->exists())
                    ->requiresConfirmation()
                    ->label('Hapus')
                    ->modalHeading('Hapus Series')
                    ->modalSubheading('Series dengan episode tidak dapat dihapus.')
                    ->modalButton('Hapus')
                    ->disabled(fn(ProjectSeries $record) => $record->episodes()->exists()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectSeries::route('/'),
            'create' => Pages\CreateProjectSeries::route('/create'),
            'edit' => Pages\EditProjectSeries::route('/{record}/edit'),
        ];
    }
}
