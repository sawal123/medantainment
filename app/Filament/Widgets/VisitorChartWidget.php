<?php

namespace App\Filament\Widgets;

use App\Models\Visitor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorChartWidget extends ChartWidget
{
    // Judul widget grafik pengunjung
    protected static ?string $heading = 'Grafik Page Views & Session Unik (6 Bulan Terakhir)';

    // Aksen warna dasar
    protected static string $color = 'info';

    // Urutan tampilan widget
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $dataPageViews      = [];
        $dataUniqueSessions = [];
        $labels             = [];

        // Data nyata dari tabel visitors, di-group per bulan
        for ($i = 5; $i >= 0; $i--) {
            $month     = Carbon::now()->subMonths($i);
            $labels[]  = $month->translatedFormat('F Y');

            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth   = $month->copy()->endOfMonth();

            // Total page views bulan ini = semua record visitor
            $pageViews = Visitor::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Session unik bulan ini
            $uniqueSessions = Visitor::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->distinct('session_id')
                ->count('session_id');

            $dataPageViews[]      = $pageViews;
            $dataUniqueSessions[] = $uniqueSessions;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Page Views',
                    'data'            => $dataPageViews,
                    'fill'            => 'start',
                    'borderColor'     => '#fbbf24',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                ],
                [
                    'label'           => 'Session Unik',
                    'data'            => $dataUniqueSessions,
                    'fill'            => 'start',
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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
