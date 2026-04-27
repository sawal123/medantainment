<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Client;
use App\Models\Project;
use App\Models\CategoryFilm;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectResource\RelationManagers;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationLabel = 'Film';
    protected static ?string $navigationGroup = 'Project';

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

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'series' => 'Series',
                        'movie' => 'Movie',
                        'company' => 'Company',
                    ])
                    ->default('series')
                    ->required(),
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
                TextColumn::make('name')
                    ->label('Nama Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
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
                    ->action(function (Project $record) {
                        $above = Project::where('urutan', '<', $record->urutan)
                            ->orderBy('urutan', 'desc')
                            ->first();

                        if ($above) {
                            $currentOrder = $record->urutan;
                            $record->update(['urutan' => $above->urutan]);
                            $above->update(['urutan' => $currentOrder]);
                        }
                    }),

                Action::make('down')
                    ->label('Down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(function (Project $record) {
                        $below = Project::where('urutan', '>', $record->urutan)
                            ->orderBy('urutan', 'asc')
                            ->first();

                        if ($below) {
                            $currentOrder = $record->urutan;
                            $record->update(['urutan' => $below->urutan]);
                            $below->update(['urutan' => $currentOrder]);
                        }
                    }),

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
