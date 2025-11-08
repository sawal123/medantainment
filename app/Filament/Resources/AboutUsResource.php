<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutUsResource\Pages;
use App\Filament\Resources\AboutUsResource\RelationManagers;
use App\Models\AboutUs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AboutUsResource extends Resource
{
    protected static ?string $model = AboutUs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Landing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->maxLength(255),

                Forms\Components\TextInput::make('subtitle')
                    ->label('Sub Judul / Tagline')
                    ->maxLength(255),

                Forms\Components\RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('vision')
                    ->label('Visi')
                    ->maxLength(255),

                Forms\Components\RichEditor::make('mission')
                    ->label('Misi')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->label('Banner / Foto Utama')
                    ->directory('about-us')
                    ->image()
                    ->nullable(),

                Forms\Components\Repeater::make('highlights')
                    ->label('Keunggulan / Poin Penting')
                    ->schema([
                        Forms\Components\TextInput::make('text')->label('Poin'),
                    ])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('video_url')
                    ->label('URL Video (YouTube Embed)')
                    ->placeholder('https://www.youtube.com/embed/...'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('subtitle')->label('Sub Judul'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y'),
            ])
            ->defaultSort('id', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListAboutUs::route('/'),
            'create' => Pages\CreateAboutUs::route('/create'),
            'edit' => Pages\EditAboutUs::route('/{record}/edit'),
        ];
    }
}
