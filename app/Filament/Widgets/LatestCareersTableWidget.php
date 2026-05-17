<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCareersTableWidget extends BaseWidget
{
    // Judul widget tabel
    protected static ?string $heading = 'Pelamar Terbaru';

    // Mengatur agar tabel memanjang penuh (full-width) di bawah widget statistik/grafik
    protected int | string | array $columnSpan = 'full';

    // Mengatur urutan tampilan widget paling bawah
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Candidate::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                Tables\Columns\TextColumn::make('carrer.title')
                    ->label('Posisi Dilamar')
                    ->default('-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'reviewed' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Masuk')
                    ->dateTime()
                    ->since() // Menampilkan format '3 hours ago' / '2 days ago' secara otomatis di Filament v3
                    ->sortable(),
            ]);
    }
}
