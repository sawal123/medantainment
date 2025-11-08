<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Slide;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SlideResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SlideResource\RelationManagers;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;


    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Landing';
    // protected static ?string $navigationLabel = 'Slide';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->image()
                    ->directory('slides/thumbnails')
                    ->visibility('public'),

                Forms\Components\TextInput::make('short')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Gunakan angka untuk menentukan urutan slide (misal: 1, 2, 3)'),

                Forms\Components\TextInput::make('link')
                    ->label('Link')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('short')
                    ->label('Urutan')
                    ->sortable(),

                TextColumn::make('link')
                    ->label('Link')
                    ->url(fn($record) => $record->link, true)
                    ->limit(30),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
