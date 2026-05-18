<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LearningSession;

class GuidebookImprovementWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Guidebook Improvement Rate (Pretest vs Posttest)';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LearningSession::query()
                    ->join('levels', 'learning_sessions.level_id', '=', 'levels.id')
                    ->join('attempts as pretest', 'learning_sessions.pretest_attempt_id', '=', 'pretest.id')
                    ->join('attempts as posttest', 'learning_sessions.posttest_attempt_id', '=', 'posttest.id')
                    ->whereIn('learning_sessions.status', ['posttest_done', 'completed'])
                    ->selectRaw('MAX(learning_sessions.id) as id, levels.name as level_name, AVG(pretest.score) as avg_pretest, AVG(posttest.score) as avg_posttest')
                    ->groupBy('levels.id', 'levels.name', 'levels.order')
                    ->orderBy('levels.order')
            )
            ->columns([
                Tables\Columns\TextColumn::make('level_name')
                    ->label('Level')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('avg_pretest')
                    ->label('Pretest Avg')
                    ->numeric(1)
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('avg_posttest')
                    ->label('Posttest Avg')
                    ->numeric(1)
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('improvement')
                    ->label('Improvement')
                    ->state(function ($record) {
                        return round($record->avg_posttest - $record->avg_pretest, 1);
                    })
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'success' : ((float)$state < 0 ? 'danger' : 'gray'))
                    ->formatStateUsing(fn ($state) => ((float)$state > 0 ? '+' : '') . $state . '%'),
            ])
            ->paginated(false);
    }
}
