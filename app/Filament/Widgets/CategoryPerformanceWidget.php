<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Question;

class CategoryPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Category Performance';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Question::query()
                    ->join('attempt_answers', 'questions.id', '=', 'attempt_answers.question_id')
                    ->whereNotNull('questions.skill_category')
                    ->selectRaw('MAX(questions.id) as id, questions.skill_category, COUNT(attempt_answers.id) as total_answers, SUM(CASE WHEN attempt_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
                    ->groupBy('questions.skill_category')
                    ->orderByRaw('SUM(CASE WHEN attempt_answers.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(attempt_answers.id) DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('skill_category')
                    ->label('Kategori Skill')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => Question::SKILL_CATEGORIES[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('accuracy')
                    ->label('Akurasi')
                    ->state(function ($record) {
                        return $record->total_answers > 0 
                            ? round(($record->correct_answers / $record->total_answers) * 100, 1)
                            : 0;
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        (float)$state >= 75 => 'success',
                        (float)$state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state . '%'),
            ])
            ->paginated(false);
    }
}
