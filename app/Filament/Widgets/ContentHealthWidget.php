<?php

namespace App\Filament\Widgets;

use App\Services\AdminAnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentHealthWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $service = app(AdminAnalyticsService::class);
        $health = $service->getContentHealthStats();

        return [
            Stat::make('Total Level & Guidebook', $health['total_levels'] . ' Level / ' . $health['total_guidebook_items'] . ' Materi')
                ->description('Struktur Pembelajaran')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Total Bank Soal', $health['total_questions'])
                ->description('Seluruh pertanyaan terdaftar')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('info'),

            Stat::make('Format Soal', $health['total_listening'] . ' Listening / ' . $health['total_writing'] . ' Writing / ' . $health['total_image'] . ' Image')
                ->description('Distribusi Tipe Soal')
                ->descriptionIcon('heroicon-m-puzzle-piece')
                ->color('warning'),
        ];
    }
}
