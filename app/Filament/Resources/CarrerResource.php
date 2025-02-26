<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Carrer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CarrerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CarrerResource\RelationManagers;

class CarrerResource extends Resource
{
    protected static ?string $model = Carrer::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Carrer';
    protected static ?string $navigationLabel = 'Loker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul Posisi Kerja')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Deskripsi Pekerjaan')
                    ->required(),

                Textarea::make('requirements')
                    ->label('Persyaratan')
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Dibuka',
                        'closed' => 'Ditutup',
                    ])
                    ->default('open')
                    ->required(),

                    TextInput::make('apply_link')
                    ->label('Link Pendaftaran')
                    ->disabled() // Tidak bisa diedit manual
                    ->dehydrated(), // Disimpan otomatis
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime(),
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
            'index' => Pages\ListCarrers::route('/'),
            'create' => Pages\CreateCarrer::route('/create'),
            'edit' => Pages\EditCarrer::route('/{record}/edit'),
        ];
    }
}
