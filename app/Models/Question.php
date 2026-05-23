<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'lesson_id',
        'type',
        'image_path',       // Storage path for visual layer
        'prompt',
        'explanation',
        'order',
        'accepted_answers', // JSON array for writing questions: ["halo", "hai"]
        'audio_path',       // Storage path for listening questions
    ];

    protected $casts = [
        'order'            => 'integer',
        'type'             => 'string',
        'accepted_answers' => 'array',
    ];

    /**
     * Question types:
     *   mcq     → Multiple Choice (select one from choices)
     *   writing → Free-text answer (checked against accepted_answers)
     *   typing  → Legacy alias for writing; treated identically
     */
    public const TYPE_MCQ     = 'mcq';
    public const TYPE_WRITING = 'writing';
    public const TYPE_TYPING  = 'typing'; // legacy

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('order');
    }

    /**
     * Returns true when this question expects a free-text answer
     * (writing mode, or legacy "typing" type).
     */
    public function isWritingType(): bool
    {
        return in_array($this->type, [self::TYPE_WRITING, self::TYPE_TYPING]);
    }

    /**
     * Returns true when this question has audio to play (listening mode).
     * Listening is a presentation *mode*, not a separate type — any question
     * (MCQ or writing) can have audio attached.
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_path);
    }

    /**
     * Returns true when this question has an image (visual mode).
     * Like audio, image is a presentation layer that can be combined
     * with any question type.
     */
    public function hasImage(): bool
    {
        return !empty($this->image_path);
    }

    /**
     * Check if a text answer is correct for writing/typing questions.
     * - Case insensitive
     * - Trims leading/trailing whitespace
     * - Checks against all accepted_answers if set, otherwise falls back
     *   to the first choice's text (backward compat).
     */
    public function isCorrectTextAnswer(string $userAnswer): bool
    {
        $normalized = mb_strtolower(trim($userAnswer));

        if (!empty($this->accepted_answers)) {
            foreach ($this->accepted_answers as $accepted) {
                if ($normalized === mb_strtolower(trim($accepted))) {
                    return true;
                }
            }
            return false;
        }

        // Fallback: compare against the single correct choice text (legacy behaviour)
        $correctChoice = $this->choices->firstWhere('is_correct', true);
        if ($correctChoice) {
            return $normalized === mb_strtolower(trim($correctChoice->text));
        }

        return false;
    }
}
