<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Hero;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\HeroResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HeroResource\RelationManagers;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Hero';
    protected static ?string $pluralModelLabel = 'Hero';
    protected static ?string $navigationGroup = 'Landing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('hero_type')
                    ->label('Hero Type')
                    ->options([
                        'hero1' => 'Hero 1',
                        'hero2' => 'Hero 2',
                        'hero3' => 'Hero 3',
                        'hero4' => 'Video Agency',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('hero_type')
                    ->label('Hero')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroes::route('/'),
            'create' => Pages\CreateHero::route('/create'),
            'edit' => Pages\EditHero::route('/{record}/edit'),
        ];
    }
}
