<?php

namespace App\Filament\Widgets;

use App\Services\AdminAnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoreMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $service = app(AdminAnalyticsService::class);

        return [
            Stat::make('Total Pengguna', number_format($service->getTotalUsers()))
                ->description('Seluruh user terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('User Aktif Hari Ini', number_format($service->getActiveUsersToday()))
                ->description('Belajar / Assessment')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('Total Level Selesai', number_format($service->getTotalCompletedLevels()))
                ->description('Level terselesaikan')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Total Posttest', number_format($service->getTotalCompletedPosttests()))
                ->description('Posttest diselesaikan')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),

            Stat::make('Rata-rata Nilai Posttest', $service->getGlobalAveragePosttestScore() . '%')
                ->description('Global Mastery Score')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
