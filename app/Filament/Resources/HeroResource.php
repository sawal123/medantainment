<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroResource\Pages;
use App\Models\Hero;
use App\Rules\SafeVideoEmbedUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Hero';

    protected static ?string $pluralModelLabel = 'Hero';

    protected static ?string $navigationGroup = 'Landing';

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

                Forms\Components\Select::make('hero_type')
                    ->label('Hero Type')
                    ->options([
                        'hero1' => 'Hero 1',
                        'hero2' => 'Hero 2',
                        'hero3' => 'Hero 3',
                        'hero4' => 'Video Agency (YouTube/Vimeo HTTPS)',
                    ])
                    ->reactive()
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label(fn (Forms\Get $get) => $get('hero_type') === 'hero4' ? 'URL Video (YouTube / Vimeo)' : 'Judul')
                    ->helperText(fn (Forms\Get $get) => $get('hero_type') === 'hero4' ? 'Hanya URL HTTPS resmi YouTube (youtube.com, youtu.be) atau Vimeo (vimeo.com)' : null)
                    ->required()
                    ->maxLength(2048)
                    ->rules(fn (Forms\Get $get) => $get('hero_type') === 'hero4' ? [new SafeVideoEmbedUrl] : []),

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
