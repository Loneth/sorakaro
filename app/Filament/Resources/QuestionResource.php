<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;

use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Questions';
    protected static ?string $navigationGroup = 'Quiz';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Core fields ─────────────────────────────────────────────────
            Select::make('lesson_id')
                ->label('Lesson')
                ->relationship('lesson', 'title')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('type')
                ->label('Question Type')
                ->options([
                    'mcq'     => '🔘 Multiple Choice (MCQ)',
                    'writing' => '✍️ Writing (Free Text)',
                    'typing'  => '⌨️ Typing (Legacy alias for Writing)',
                ])
                ->default('mcq')
                ->required()
                ->live()
                ->helperText('MCQ = user picks an answer. Writing = user types their answer.'),

            Textarea::make('prompt')
                ->label('Question / Prompt')
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Apa arti dari kata "Horas"?'),

            Textarea::make('explanation')
                ->label('Explanation (shown after answering)')
                ->rows(2)
                ->columnSpanFull()
                ->placeholder('Horas adalah salam umum dalam Bahasa Karo yang berarti...'),

            TextInput::make('order')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->required(),

            // ── Writing / Typing: accepted answers ──────────────────────────
            Section::make('✍️ Writing Answers')
                ->description('Define accepted answers for writing/typing questions. Case-insensitive, whitespace is trimmed.')
                ->schema([
                    Repeater::make('accepted_answers')
                        ->label('Accepted Answers')
                        ->simple(
                            TextInput::make('answer')
                                ->placeholder('halo')
                                ->required()
                        )
                        ->helperText('Add all acceptable answers including synonyms, e.g. "halo", "hai", "horas".')
                        ->addActionLabel('+ Add Answer')
                        ->minItems(1)
                        ->reorderableWithButtons()
                        ->columnSpanFull(),
                ])
                ->visible(fn(Get $get) => in_array($get('type'), ['writing', 'typing']))
                ->collapsible(),

            // ── Listening: audio upload ──────────────────────────────────────
            Section::make('🎧 Listening Audio')
                ->description('Upload an audio clip. The audio player will be shown before the question. Any question type can have audio.')
                ->schema([
                    FileUpload::make('audio_path')
                        ->label('Audio File')
                        ->disk('dae')
                        ->directory('question-audio')
                        ->visibility('public')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/ogg'])
                        ->maxSize(10240) // 10 MB
                        ->helperText('Accepted: MP3, WAV, OGG — max 10 MB')
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            // ── Image: visual upload ─────────────────────────────────────────
            Section::make('🖼️ Image Visual')
                ->description('Upload an image to show with the question. Any question type can have an image.')
                ->schema([
                    FileUpload::make('image_path')
                        ->label('Image File')
                        ->image()
                        ->disk('dae')
                        ->directory('question-images')
                        ->visibility('public')
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120) // 5 MB
                        ->helperText('Accepted: JPG, PNG, WEBP — max 5 MB')
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('lesson.level.name')
                    ->label('Level')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'mcq'     => 'info',
                        'writing' => 'success',
                        'typing'  => 'warning',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'mcq'     => '🔘 MCQ',
                        'writing' => '✍️ Writing',
                        'typing'  => '⌨️ Typing',
                        default   => $state,
                    }),

                IconColumn::make('audio_path')
                    ->label('🎧 Audio')
                    ->boolean()
                    ->trueIcon('heroicon-o-speaker-wave')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),

                ImageColumn::make('image_path')
                    ->label('🖼️ Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                TextColumn::make('prompt')
                    ->label('Question')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'mcq'     => '🔘 Multiple Choice',
                        'writing' => '✍️ Writing',
                        'typing'  => '⌨️ Typing (Legacy)',
                    ])
                    ->label('Filter by Type'),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit'   => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
