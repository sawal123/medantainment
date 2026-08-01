<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Carrer';
    protected static ?string $navigationLabel = 'Kandidat';

    // ───────────────────────────────────────────────
    // Authorization — Admin Only
    // ───────────────────────────────────────────────

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

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    // ───────────────────────────────────────────────
    // Form
    // ───────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('carrer_id')
                    ->label('Posisi / Karier')
                    ->relationship('carrer', 'title')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                // Resume disimpan di disk private, tidak bisa diakses publik
                // Download dilakukan melalui endpoint terproteksi
                Forms\Components\FileUpload::make('resume')
                    ->label('CV / Resume')
                    ->disk('private')
                    ->directory('candidates/resumes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(4096) // 4 MB
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string =>
                            (string) Str::uuid() . '.pdf'
                    )
                    ->helperText('Hanya file PDF. Maksimal 4 MB.'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'reviewed' => 'Reviewed',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('created_at')
                    ->label('Tanggal Daftar')
                    ->default(now())
                    ->disabled(),
            ]);
    }

    // ───────────────────────────────────────────────
    // Table
    // ───────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('carrer.title')
                    ->label('Posisi')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Telepon'),

                // Ganti URL publik dengan action download terproteksi
                Tables\Columns\TextColumn::make('resume')
                    ->label('CV / Resume')
                    ->formatStateUsing(fn ($state) => $state ? '📄 Tersedia' : '-')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'gray',
                        'reviewed' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Download CV melalui endpoint terproteksi (bukan direct URL)
                Tables\Actions\Action::make('download_resume')
                    ->label('Unduh CV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn (Candidate $record): string =>
                        route('secure.candidate.download', [
                            'candidate' => $record->id,
                            'field'     => 'resume',
                        ])
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Candidate $record): bool => ! empty($record->resume)),

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
            'index'  => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit'   => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}
