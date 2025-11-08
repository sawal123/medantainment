<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Landing;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LandingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LandingResource\RelationManagers;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class LandingResource extends Resource
{
    protected static ?string $model = Landing::class;
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Landing';
    protected static ?string $navigationLabel = 'Texts';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('key')
                    ->label('Title')
                    ->required(),
                Textarea::make('value')
                    ->label('Value')


            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Key'),
                TextColumn::make('value')->label('Value'),
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
            'index' => Pages\ListLandings::route('/'),
            'create' => Pages\CreateLanding::route('/create'),
            'edit' => Pages\EditLanding::route('/{record}/edit'),
        ];
    }
}
