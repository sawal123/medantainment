<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Alamat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AlamatResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AlamatResource\RelationManagers;

class AlamatResource extends Resource
{
    protected static ?string $model = Alamat::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Alamat Kantor';
    protected static ?string $slug = 'alamat-kantor'; // Mengubah URL menu Filament

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('office_name')
                ->label('Nama Kantor')
                ->default('Medantainment')
                // ->disabled()
                ->required(),

            TextInput::make('street')
                ->label('Alamat Jalan')
                ->required(),

            TextInput::make('city')
                ->label('Kota')
                ->required(),

            TextInput::make('state')
                ->label('Provinsi')
                ->nullable(),

            TextInput::make('postal_code')
                ->label('Kode Pos')
                ->numeric()
                ->nullable(),

            TextInput::make('country')
                ->label('Negara')
                ->default('Indonesia')
                ->required(),

            TextInput::make('phone')
                ->label('Nomor Telepon')
                ->tel()
                ->nullable(),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->nullable(),

            TextInput::make('maps_link')
                ->label('Link Google Maps')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('office_name')->label('Nama Kantor')->sortable(),
                TextColumn::make('street')->label('Alamat')->sortable(),
                TextColumn::make('city')->label('Kota')->sortable(),
                TextColumn::make('country')->label('Negara')->sortable(),
                TextColumn::make('phone')->label('Telepon')->sortable(),
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
            'index' => Pages\ListAlamats::route('/'),
            'create' => Pages\CreateAlamat::route('/create'),
            'edit' => Pages\EditAlamat::route('/{record}/edit'),
        ];
    }
}
