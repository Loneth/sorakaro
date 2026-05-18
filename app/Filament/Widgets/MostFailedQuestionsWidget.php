<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Question;

class MostFailedQuestionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Most Failed Questions (Min 5 attempts)';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Question::query()
                    ->join('attempt_answers', 'questions.id', '=', 'attempt_answers.question_id')
                    ->join('lessons', 'questions.lesson_id', '=', 'lessons.id')
                    ->join('levels', 'lessons.level_id', '=', 'levels.id')
                    ->selectRaw('questions.id, questions.prompt, questions.type, levels.name as level_name, COUNT(attempt_answers.id) as total_attempts, SUM(CASE WHEN attempt_answers.is_correct = 0 THEN 1 ELSE 0 END) as failed_attempts')
                    ->groupBy('questions.id', 'questions.prompt', 'questions.type', 'levels.name')
                    ->havingRaw('COUNT(attempt_answers.id) >= 5')
                    ->orderByRaw('SUM(CASE WHEN attempt_answers.is_correct = 0 THEN 1 ELSE 0 END) / COUNT(attempt_answers.id) DESC')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('prompt')
                    ->label('Soal')
                    ->limit(80)
                    ->tooltip(fn ($record) => strip_tags($record->prompt))
                    ->formatStateUsing(fn ($state) => strip_tags($state) ?: '(Gambar/Audio)'),
                Tables\Columns\TextColumn::make('level_name')
                    ->label('Level'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('failure_rate')
                    ->label('Failure Rate')
                    ->state(function ($record) {
                        return $record->total_attempts > 0 
                            ? round(($record->failed_attempts / $record->total_attempts) * 100, 1)
                            : 0;
                    })
                    ->badge()
                    ->color('danger')
                    ->description(fn ($record) => $record->total_attempts . 'x dicoba')
                    ->formatStateUsing(fn ($state) => $state . '%'),
            ])
            ->paginated(false);
    }
}
