<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use App\Models\Candidate;
use App\Models\Project;
use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardOverviewWidget extends BaseWidget
{
    // Polling interval untuk update data otomatis tiap 60 detik
    protected static ?string $pollingInterval = '60s';

    // Mengatur urutan tampilan widget di dashboard
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $blogCount = Blog::count();
        $blogPublished = Blog::where('status', 'published')->count();
        $blogDraft = Blog::where('status', 'draft')->count();

        $projectCount = Project::count();

        $candidateCount = Candidate::count();
        $candidatePending = Candidate::where('status', 'pending')->count();

        // ── Data visitor nyata dari tabel visitors ──
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Page Views bulan ini = semua record visitor bulan ini
        $pageViewsThisMonth = Visitor::where('created_at', '>=', $startOfMonth)->count();

        // Unique session bulan ini (bukan unique visitor sesungguhnya, tapi konsisten)
        // Label yang jujur: "Session Unik" bukan "Unique Visitor"
        $uniqueSessionsThisMonth = Visitor::where('created_at', '>=', $startOfMonth)
            ->distinct('session_id')
            ->count('session_id');

        // Chart 7 hari terakhir untuk page views
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $chartData[] = Visitor::whereDate('created_at', $now->copy()->subDays($i)->toDateString())->count();
        }

        return [
            Stat::make('Total Artikel Blog', $blogCount)
                ->description("Published: {$blogPublished} | Draft: {$blogDraft}")
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([$blogDraft, $blogPublished, $blogCount])
                ->color('success'),

            Stat::make('Total Portofolio Film', $projectCount)
                ->description('Project karya kreatif & film')
                ->descriptionIcon('heroicon-m-film')
                ->chart([max(1, $projectCount - 2), max(1, $projectCount - 1), $projectCount])
                ->color('primary'),

            Stat::make('Total Pelamar Kerja', $candidateCount)
                ->description("Menunggu review: {$candidatePending}")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([max(0, $candidateCount - $candidatePending), $candidatePending, $candidateCount])
                ->color('warning'),

            // Label jujur: Page Views (bukan estimasi)
            Stat::make('Page Views (Bulan Ini)', number_format($pageViewsThisMonth))
                ->description('Session unik: '.number_format($uniqueSessionsThisMonth))
                ->descriptionIcon('heroicon-m-eye')
                ->chart($chartData)
                ->color('info'),
        ];
    }
}
