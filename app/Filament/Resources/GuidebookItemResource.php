<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuidebookItemResource\Pages;
use App\Models\GuidebookItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuidebookItemResource extends Resource
{
    protected static ?string $model = GuidebookItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Guidebook Items';
    protected static ?string $modelLabel = 'Guidebook Item';
    protected static ?string $pluralModelLabel = 'Guidebook Items';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationGroup = 'Guidebook';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('guidebook_section_id')
                ->label('Section')
                ->relationship('section', 'title')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'phrase' => '💬 Phrase',
                    'tip' => '💡 Tip',
                ])
                ->required()
                ->default('phrase'),

            Forms\Components\Textarea::make('text')
                ->label('Main Text')
                ->required()
                ->rows(2)
                ->columnSpanFull()
                ->placeholder('Hello, how are you?'),

            Forms\Components\Textarea::make('translation')
                ->label('Translation')
                ->rows(2)
                ->columnSpanFull()
                ->placeholder('Halo, apa kabar?'),

            Forms\Components\FileUpload::make('audio_path')
                ->label('Audio Pronunciation')
                ->disk('public')
                ->directory('guidebook-audio')
                ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/ogg'])
                ->maxSize(10240)
                ->helperText('Upload MP3/WAV/OGG (max 10 MB). Users will hear this when clicking the play button.')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('order')
                ->label('Display Order')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('section.level.name')
                    ->label('Level')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'phrase' => 'info',
                        'tip' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'phrase' => '💬 Phrase',
                        'tip' => '💡 Tip',
                    }),

                Tables\Columns\TextColumn::make('text')
                    ->label('Text')
                    ->limit(50)
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('translation')
                    ->label('Translation')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section')
                    ->relationship('section', 'title')
                    ->label('Filter by Section')
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'phrase' => 'Phrase',
                        'tip' => 'Tip',
                    ])
                    ->label('Filter by Type'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGuidebookItems::route('/'),
            'create' => Pages\CreateGuidebookItem::route('/create'),
            'edit' => Pages\EditGuidebookItem::route('/{record}/edit'),
        ];
    }

    /**
     * Authorization: Only admin role can access
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
}
