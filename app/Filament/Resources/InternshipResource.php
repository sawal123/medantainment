<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternshipResource\Pages;
use App\Filament\Resources\InternshipResource\RelationManagers;
use App\Models\Internship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InternshipResource extends Resource
{
    protected static ?string $model = Internship::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Carrer';
    protected static ?string $navigationLabel = 'Internship';

    // ───────────────────────────────────────────────
    // Authorization — Admin Only
    // ───────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        // Internship hanya dibuat dari form publik, bukan dari admin
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    // ───────────────────────────────────────────────
    // Form
    // ───────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Internship')
                ->schema([
                    Forms\Components\TextInput::make('nama')->disabled(),
                    Forms\Components\TextInput::make('ttl')->disabled(),
                    Forms\Components\Textarea::make('alamat')->disabled(),
                    Forms\Components\TextInput::make('sekolah_universitas')->disabled(),
                    Forms\Components\TextInput::make('jurusan')->disabled(),
                    Forms\Components\TextInput::make('periode_magang')->disabled(),

                    Forms\Components\Textarea::make('keahlian')->disabled(),
                    Forms\Components\Textarea::make('ketertarangan_singkat')->disabled(),

                    Forms\Components\TextInput::make('ketertarikan')
                        ->formatStateUsing(function ($state) {
                            if (is_array($state)) {
                                return implode(', ', $state);
                            }

                            if (is_string($state)) {
                                return implode(', ', json_decode($state, true) ?? []);
                            }

                            return '-';
                        })
                        ->disabled(),

                    // File dari disk private — tampilkan sebagai info, download via endpoint
                    Forms\Components\Placeholder::make('surat_izin_info')
                        ->label('Surat Izin')
                        ->content(fn ($record) => $record?->surat_izin
                            ? '📄 File tersedia'
                            : 'Tidak ada file'),

                    Forms\Components\Placeholder::make('surat_lamaran_info')
                        ->label('Surat Lamaran')
                        ->content(fn ($record) => $record?->surat_lamaran
                            ? '📄 File tersedia'
                            : 'Tidak ada file'),

                    Forms\Components\Placeholder::make('cv_portofolio_info')
                        ->label('CV / Portofolio')
                        ->content(fn ($record) => $record?->cv_portofolio
                            ? '📄 File tersedia'
                            : 'Tidak ada file'),

                    Forms\Components\Placeholder::make('foto_diri_info')
                        ->label('Foto Diri')
                        ->content(fn ($record) => $record?->foto_diri
                            ? '🖼️ Foto tersedia'
                            : 'Tidak ada foto'),

                    Forms\Components\Textarea::make('alasan_internship')->disabled(),

                    Forms\Components\Placeholder::make('Rating')
                        ->content('Semua nilai rating tampil di bawah:')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('rating_kreatifitas')->disabled(),
                    Forms\Components\TextInput::make('rating_analitis')->disabled(),
                    Forms\Components\TextInput::make('rating_komunikasi')->disabled(),
                    Forms\Components\TextInput::make('rating_manajemen_waktu')->disabled(),
                    Forms\Components\TextInput::make('rating_adaptasi')->disabled(),
                    Forms\Components\TextInput::make('rating_teamwork')->disabled(),
                    Forms\Components\TextInput::make('rating_motivasi')->disabled(),
                    Forms\Components\TextInput::make('rating_tekanan')->disabled(),
                ])
                ->columns(2),
        ]);
    }

    // ───────────────────────────────────────────────
    // Table
    // ───────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sekolah_universitas'),
                Tables\Columns\TextColumn::make('jurusan'),
                Tables\Columns\TextColumn::make('periode_magang'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Accept' => 'success',
                        'Reject' => 'danger',
                        'Viewed' => 'warning',
                        default  => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Download dokumen melalui endpoint terproteksi
                Tables\Actions\Action::make('download_cv')
                    ->label('Unduh CV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn (Internship $record): string =>
                        route('secure.internship.download', [
                            'internship' => $record->id,
                            'field'      => 'cv_portofolio',
                        ])
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Internship $record): bool => ! empty($record->cv_portofolio)),

                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'Accept'])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'Reject'])),

                Tables\Actions\Action::make('viewed')
                    ->label('Viewed')
                    ->color('warning')
                    ->icon('heroicon-o-eye')
                    ->action(fn ($record) => $record->update(['status' => 'Viewed'])),

                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListInternships::route('/'),
            'create' => Pages\CreateInternship::route('/create'),
            'edit'   => Pages\EditInternship::route('/{record}/edit'),
        ];
    }
}
