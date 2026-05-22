<?php

namespace App\Filament\Resources\BlogResource\Widgets;

use App\Models\Visitor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BlogViewsChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Kunjungan Harian (30 Hari Terakhir)';

    protected function getData(): array
    {
        // Ambil data kunjungan 30 hari terakhir
        $data = Visitor::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Buat data kontinu 30 hari tanpa jeda kosong
        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $dateString = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $values[] = $data[$dateString] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kunjungan',
                    'data' => $values,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
