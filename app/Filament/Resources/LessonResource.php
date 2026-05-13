<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Lessons';
    protected static ?string $navigationGroup = 'Quiz';
    protected static ?string $modelLabel = 'Lesson';
    protected static ?string $pluralModelLabel = 'Lessons';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('level_id')
                ->label('Level')
                ->relationship('level', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),

            TextInput::make('order')
                ->label('Order')
                ->numeric()
                ->default(0),

            TextInput::make('pass_rate')
                ->label('Pass Rate (%)')
                ->numeric()
                ->default(70)
                ->minValue(0)
                ->maxValue(100)
                ->required(),

            Select::make('assessment_type')
                ->label('Assessment Type')
                ->helperText('Biarkan kosong untuk lesson biasa. Pilih Pretest/Posttest untuk assessment tertentu.')
                ->options([
                    'pretest'  => '🔍 Pretest',
                    'posttest' => '✅ Posttest',
                ])
                ->nullable()
                ->placeholder('— Normal Lesson —'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('pass_rate')
                    ->label('Pass Rate')
                    ->formatStateUsing(fn (string $state): string => "{$state}%")
                    ->sortable(),

                TextColumn::make('assessment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pretest'  => 'warning',
                        'posttest' => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pretest'  => '🔍 Pretest',
                        'posttest' => '✅ Posttest',
                        default    => '📖 Normal',
                    }),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('assessment_type')
                    ->label('Filter by Type')
                    ->options([
                        'pretest'  => '🔍 Pretest',
                        'posttest' => '✅ Posttest',
                    ])
                    ->placeholder('All Types'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
