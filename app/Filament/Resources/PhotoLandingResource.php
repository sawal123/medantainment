<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Landing;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PhotoLanding;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PhotoLandingResource\Pages;
use App\Filament\Resources\PhotoLandingResource\RelationManagers;
use Illuminate\Support\Str;

class PhotoLandingResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $model = PhotoLanding::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Image';
    protected static ?string $navigationGroup = 'Landing';

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
                TextInput::make('key')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('value')
                    ->label('Gambar')
                    ->disk('public')
                    ->directory('photo-landing')
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
                    ),
            ]);
    }

    // ───────────────────────────────────────────────
    // Table
    // ───────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Key'),
                ImageColumn::make('value')
                    ->label('Gambar')
                    ->circular(),
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
            'index'  => Pages\ListPhotoLandings::route('/'),
            'create' => Pages\CreatePhotoLanding::route('/create'),
            'edit'   => Pages\EditPhotoLanding::route('/{record}/edit'),
        ];
    }
}
