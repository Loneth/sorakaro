<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Level;

class LevelPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Level Performance';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Level::query()
                    ->join('lessons', 'levels.id', '=', 'lessons.level_id')
                    ->join('attempts', function($join) {
                        $join->on('lessons.id', '=', 'attempts.lesson_id')
                             ->whereNotNull('attempts.finished_at');
                    })
                    ->where('lessons.assessment_type', 'posttest')
                    ->selectRaw('levels.id, levels.name, levels.order, COUNT(attempts.id) as total_attempts, SUM(CASE WHEN attempts.passed = 1 THEN 1 ELSE 0 END) as passed_attempts, AVG(attempts.score) as avg_score')
                    ->groupBy('levels.id', 'levels.name', 'levels.order')
                    ->orderBy('levels.order')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Level')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('pass_rate')
                    ->label('Pass Rate')
                    ->state(function ($record) {
                        return $record->total_attempts > 0 
                            ? round(($record->passed_attempts / $record->total_attempts) * 100, 1)
                            : 0;
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (float)$state >= 75 => 'success',
                        (float)$state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state . '%'),
                Tables\Columns\TextColumn::make('avg_score')
                    ->label('Rata-rata Skor')
                    ->numeric(1)
                    ->suffix('%'),
            ])
            ->paginated(false);
    }
}
