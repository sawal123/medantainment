<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarrerResource\Pages;
use App\Models\Carrer;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CarrerResource extends Resource
{
    protected static ?string $model = Carrer::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Carrer';

    protected static ?string $navigationLabel = 'Loker';

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
                TextInput::make('title')
                    ->label('Judul Posisi Kerja')
                    ->required()
                    ->maxLength(255),

                Select::make('time')
                    ->label('Jenis Pekerjaan')
                    ->options([
                        'Freelance' => 'Freelance',
                        'Kontrak' => 'Kontrak',
                        'Karyawan' => 'Karyawan',
                        'Internship' => 'Internship',
                    ])
                    ->required(),
                RichEditor::make('description')
                    ->label('Deskripsi Pekerjaan')
                    ->required(),
                TextInput::make('salary')
                    ->default('-')
                    ->label('Salary'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Dibuka',
                        'closed' => 'Ditutup',
                    ])
                    ->default('open')
                    ->required(),

                TextInput::make('apply_link')
                    ->label('Link Pendaftaran (otomatis terisi)')
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
