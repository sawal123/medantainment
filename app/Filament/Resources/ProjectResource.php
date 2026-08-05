<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\CategoryFilm;
use App\Models\Project;
use App\Models\ProjectSeries;
use App\Rules\SafeVideoEmbedUrl;
use App\Services\ProjectSeriesAssignmentService;
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
                    ->required()
                    ->dehydrated(true)
                    ->disabled(fn (callable $get) => $get('content_kind') === 'episode'),

                Select::make('content_kind')
                    ->label('Jenis Konten')
                    ->options([
                        'standalone' => 'Project Biasa',
                        'episode' => 'Episode Series',
                    ])
                    ->default(fn (callable $get) => $get('series_id') ? 'episode' : 'standalone')
                    ->reactive()
                    ->afterStateHydrated(function ($state, $set, $get) {
                        $set('content_kind', $get('series_id') ? 'episode' : 'standalone');
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'standalone') {
                            $set('series_id', null);
                        }
                    })
                    ->dehydrated(false),

                Select::make('series_id')
                    ->label('Series / Playlist')
                    ->relationship('series', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required(fn (callable $get) => $get('content_kind') === 'episode')
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $series = ProjectSeries::find($state);
                            if ($series) {
                                $set('category_film_id', $series->category_film_id);

                                // Auto-fill episode 'urutan' for new records or when empty.
                                // Also override prior global default if it was just populated
                                $current = $get('urutan');
                                $globalNext = (Project::max('urutan') ?? 0) + 1;
                                if (empty($current) || $current == $globalNext) {
                                    $next = (Project::where('series_id', $state)->max('urutan') ?? 0) + 1;
                                    $set('urutan', $next);
                                }
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
                    ->label(fn (callable $get) => $get('content_kind') === 'episode' ? 'Nomor / Urutan Episode' : 'Urutan')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    // Do not override existing urutan on edit. Initialize when empty.
                    ->afterStateHydrated(function ($state, callable $set, callable $get, $record = null) {
                        if (empty($state)) {
                            $seriesId = $get('series_id');
                            if ($seriesId) {
                                $next = (Project::where('series_id', $seriesId)->max('urutan') ?? 0) + 1;
                                $set('urutan', $next);

                                return;
                            }

                            $set('urutan', (Project::max('urutan') ?? 0) + 1);
                        }
                    }),
            ]);
    }

    public static function prepareSeriesData(array $data): array
    {
        return ProjectSeriesAssignmentService::normalize($data);
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
                    ->options(fn () => CategoryFilm::orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('Semua Kategori'),
            ])
            ->actions([
                Action::make('up')
                    ->label('Up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(fn (Project $record) => $record->moveUp()),

                Action::make('down')
                    ->label('Down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(fn (Project $record) => $record->moveDown()),

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
