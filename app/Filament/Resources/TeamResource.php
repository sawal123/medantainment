<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Team;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Website';

    // ───────────────────────────────────────────────
    // Authorization — Admin Only
    // ───────────────────────────────────────────────

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

    // ───────────────────────────────────────────────
    // Form
    // ───────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('posisi')
                    ->label('Posisi')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('gambar')
                    ->label('Foto')
                    ->disk('public')
                    ->directory('teams')
                    ->image()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(2048) // 2 MB
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string =>
                            (string) Str::uuid() . '.' .
                            strtolower($file->getClientOriginalExtension())
                    )
                    ->required(),
            ]);
    }

    // ───────────────────────────────────────────────
    // Table
    // ───────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama')->searchable(),
                TextColumn::make('posisi')->label('Posisi')->searchable(),
                ImageColumn::make('gambar')->label('Foto'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index'  => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit'   => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
