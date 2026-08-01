<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    // ───────────────────────────────────────────────
    // Filament Resource Authorization — Admin Only
    // ───────────────────────────────────────────────

    /**
     * Hanya admin yang boleh mengakses resource ini.
     * Ini melindungi route index, create, edit, dan semua action.
     */
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
        /** @var User $authUser */
        $authUser = auth()->user();

        if (! $authUser?->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Cegah penghapusan diri sendiri atau admin terakhir.
     */
    public static function canDelete(Model $record): bool
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        /** @var User $record */

        if (! $authUser?->isAdmin()) {
            return false;
        }

        // Admin tidak boleh menghapus dirinya sendiri jika itu admin terakhir
        if ($record->isAdmin() && $record->isLastAdmin()) {
            return false;
        }

        return true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    // ───────────────────────────────────────────────
    // Form
    // ───────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        $isEditingSelf = $form->getRecord()?->id === $authUser?->id;

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                // Admin tidak boleh mengubah role miliknya sendiri
                Forms\Components\Select::make('role')
                    ->label('Peran (Role)')
                    ->options([
                        'admin'  => 'Admin',
                        'author' => 'Author',
                    ])
                    ->default('author')
                    ->required()
                    ->disabled($isEditingSelf)
                    ->dehydrated(fn ($state) => ! $isEditingSelf)
                    ->helperText($isEditingSelf
                        ? 'Anda tidak dapat mengubah role akun sendiri.'
                        : null
                    ),
            ]);
    }

    // ───────────────────────────────────────────────
    // Table
    // ───────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin'  => 'success',
                        'author' => 'info',
                        default  => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        // Proteksi server-side: cegah hapus admin terakhir
                        if ($record->isAdmin() && $record->isLastAdmin()) {
                            Notification::make()
                                ->title('Tidak dapat menghapus admin terakhir.')
                                ->danger()
                                ->send();
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
