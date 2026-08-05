<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\CategoryFilm;
use App\Models\Project;
use App\Models\ProjectSeries;
use App\Rules\SafeVideoEmbedUrl;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Film';

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
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Proyek')
                    ->required()
                    ->maxLength(255),

                TextInput::make('link')
                    ->label('Link Video')
                    ->required()
                    ->rule(new SafeVideoEmbedUrl)
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),

                DatePicker::make('end_date')
                    ->label('Tanggal Selesai'),

                Select::make('category_film_id')
                    ->label('Kategori Film')
                    ->relationship('categoryFilm', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('content_kind')
                    ->label('Jenis Konten')
                    ->options([
                        'standalone' => 'Project Biasa',
                        'episode' => 'Episode Series',
                    ])
                    ->default(fn (callable $get) => $get('series_id') ? 'episode' : 'standalone')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'standalone') {
                            $set('series_id', null);
                            $set('type', 'movie');
                        }

                        if ($state === 'episode') {
                            $set('type', 'series');
                        }
                    })
                    ->dehydrated(false),

                Select::make('series_id')
                    ->label('Series / Playlist')
                    ->relationship('series', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $series = ProjectSeries::find($state);
                            if ($series) {
                                $set('category_film_id', $series->category_film_id);
                                $set('type', 'series');
                            }
                        }
                    })
                    ->visible(fn (callable $get) => $get('content_kind') === 'episode'),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'series' => 'Series',
                        'movie' => 'Movie',
                        'company' => 'Company',
                    ])
                    ->default('movie')
                    ->required(),

                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(fn() => (Project::max('urutan') ?? 0) + 1)
                    ->required(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return static::prepareSeriesData($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::prepareSeriesData($data);
    }

    protected static function prepareSeriesData(array $data): array
    {
        if (($data['content_kind'] ?? 'standalone') !== 'episode') {
            $data['series_id'] = null;
            $data['type'] = 'movie';
        }

        if (($data['content_kind'] ?? 'standalone') === 'episode' && empty($data['series_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'series_id' => 'Series harus dipilih ketika jenis konten adalah episode.',
            ]);
        }

        if (! empty($data['series_id'])) {
            $series = ProjectSeries::find($data['series_id']);
            if ($series) {
                if (($data['category_film_id'] ?? null) !== $series->category_film_id) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'category_film_id' => 'Kategori project harus sama dengan kategori series yang dipilih.',
                    ]);
                }

                $data['category_film_id'] = $series->category_film_id;
                $data['type'] = 'series';
            }
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan', 'asc')
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('series.name')
                    ->label('Series')
                    ->sortable()
                    ->limit(18),
                TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),
                ViewColumn::make('link')
                    ->view('filament.tables.columns.video'),
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->limit(10),
                TextColumn::make('categoryFilm.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
            ])
            ->filters([
                SelectFilter::make('category_film_id')
                    ->label('Kategori Film')
                    ->options(fn() => CategoryFilm::orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('Semua Kategori'),
            ])
            ->actions([
                Action::make('up')
                    ->label('Up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(fn(Project $record) => $record->moveUp()),

                Action::make('down')
                    ->label('Down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(fn(Project $record) => $record->moveDown()),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
