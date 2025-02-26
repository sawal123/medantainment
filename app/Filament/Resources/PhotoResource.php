<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Photo;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PhotoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PhotoResource\RelationManagers;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationGroup = 'Project';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->label('Photo Name')
                    ->required()
                    ->maxLength(255),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'Resto' => 'Resto',
                        'Company' => 'Company',
                        'Holiday' => 'Holiday',
                        'Wedding' => 'Wedding',
                        'Dokumentasi' => 'Dokumentas',
                    ])
                    ->searchable() // Masih bisa dicari
                    ->required(),

                FileUpload::make('photo')
                    ->label('Upload Photo')
                    ->disk('public') // Simpan di storage/public
                    ->directory('photos') // Folder dalam storage/app/public/photos
                    ->image() // Hanya menerima gambar
                    ->required()
                    ->visibility('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Photo Name')->sortable()->searchable(),
                TextColumn::make('category')->label('Category')->sortable()->searchable(),
                ImageColumn::make('photo')->disk('public')->label('Photo')->circular(),
                TextColumn::make('client.name')->label('Client')->sortable()->searchable(),
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
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }
}
