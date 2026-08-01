<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SosmedResource\Pages;
use App\Models\Sosmed;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SosmedResource extends Resource
{
    protected static ?string $model = Sosmed::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('icon')
                    ->label('Icon')
                    ->options([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'youtube' => 'YouTube',
                        'linkedin' => 'Linkedin',
                        'tiktok' => 'TikTok',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->searchable()
                    ->required(),

                TextInput::make('key')
                    ->label('Title')
                    ->required(),
                TextInput::make('value')
                    ->label('Value'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable(),
                TextColumn::make('key')
                    ->label('Title'),
                TextColumn::make('value')
                    ->label('Value'),
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
            'index' => Pages\ListSosmeds::route('/'),
            'create' => Pages\CreateSosmed::route('/create'),
            'edit' => Pages\EditSosmed::route('/{record}/edit'),
        ];
    }
}
