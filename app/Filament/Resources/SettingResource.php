<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Setting;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SettingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SettingResource\RelationManagers;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('site_name')
                    ->label('Nama Situs')
                    ->required(),

                FileUpload::make('logo')
                    ->label('Upload Logo')
                    ->disk('public')
                    ->directory('settings')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string =>
                            (string) \Illuminate\Support\Str::uuid() . '.' . strtolower($file->getClientOriginalExtension())
                    ),

                FileUpload::make('favicon')
                    ->label('Upload Favicon')
                    ->disk('public')
                    ->directory('settings')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(1024)
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string =>
                            (string) \Illuminate\Support\Str::uuid() . '.' . strtolower($file->getClientOriginalExtension())
                    ),

                TextInput::make('seo_title')
                    ->label('Judul SEO')
                    ->required(),

                Textarea::make('seo_description')
                    ->label('Deskripsi SEO')
                    ->rows(3),

                Textarea::make('seo_keywords')
                    ->label('Kata Kunci SEO')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site_name')->label('Nama Situs')->sortable()->searchable(),
                ImageColumn::make('logo')->label('Logo')->size(50),
                ImageColumn::make('favicon')->label('Favicon')->size(50),
                TextColumn::make('seo_title')->label('Judul SEO')->sortable()->searchable(),
                TextColumn::make('seo_description')->label('Deskripsi SEO')->limit(50),
                TextColumn::make('seo_keywords')->label('Kata Kunci SEO')->limit(50),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
