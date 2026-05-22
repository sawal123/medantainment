<?php

namespace App\Filament\Resources\BlogResource\Widgets;

use App\Models\Blog;
use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BlogStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // 1. Total views: Akumulasi semua views dari seluruh artikel
        $totalViews = Visitor::whereNotNull('blog_id')->count();

        // 2. Most viewed blog: Judul artikel yang paling banyak dibaca
        $mostViewedId = Visitor::select('blog_id', DB::raw('count(*) as total'))
            ->whereNotNull('blog_id')
            ->groupBy('blog_id')
            ->orderByDesc('total')
            ->first()?->blog_id;

        $mostViewedBlog = $mostViewedId ? Blog::find($mostViewedId)?->title : 'Belum ada data';
        if (strlen($mostViewedBlog) > 28) {
            $mostViewedBlog = substr($mostViewedBlog, 0, 25) . '...';
        }

        // 3. Unique visitors: Jumlah pengunjung unik (berdasarkan IP)
        $uniqueVisitors = Visitor::distinct('ip_address')->count('ip_address');

        return [
            Stat::make('Total Tayangan', number_format($totalViews) . ' Views')
                ->description('Total tayangan dari semua artikel')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Artikel Terpopuler', $mostViewedBlog)
                ->description('Paling banyak dibaca')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Pengunjung Unik', number_format($uniqueVisitors) . ' User')
                ->description('Berdasarkan IP unik')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
