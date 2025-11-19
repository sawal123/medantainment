<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\client;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClientResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ClientResource\RelationManagers;

class ClientResource extends Resource
{
    protected static ?string $model = client::class;
    protected bool $canCreateAnother = true;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Project';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')->image()
                    ->directory('client')
                    ->imageEditor(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel(),

                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->minValue(1)
                    ->default(fn() => Client::max('urutan') + 1),


                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan', 'asc')   // ⬅ TAMBAH INI
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('phone')->label('Nomor HP')->sortable(),
                TextColumn::make('urutan')->label('Urutan')->sortable(), // opsional tampilkan
                TextColumn::make('created_at')->label('Dibuat Pada')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('up')
                    ->label('Up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (Client $record) {
                        $above = Client::where('urutan', '<', $record->urutan)
                            ->orderBy('urutan', 'desc')
                            ->first();

                        if ($above) {
                            $currentOrder = $record->urutan;
                            $record->update(['urutan' => $above->urutan]);
                            $above->update(['urutan' => $currentOrder]);
                        }
                    }),

                Tables\Actions\Action::make('down')
                    ->label('Down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(function (Client $record) {
                        $below = Client::where('urutan', '>', $record->urutan)
                            ->orderBy('urutan', 'asc')
                            ->first();

                        if ($below) {
                            $currentOrder = $record->urutan;
                            $record->update(['urutan' => $below->urutan]);
                            $below->update(['urutan' => $currentOrder]);
                        }
                    }),

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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
