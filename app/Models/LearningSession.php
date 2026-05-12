<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningSession extends Model
{
    protected $fillable = [
        'user_id',
        'pretest_attempt_id',
        'posttest_attempt_id',
        'status',
        'level_id',
        'improvement',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function pretestAttempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'pretest_attempt_id');
    }

    public function posttestAttempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'posttest_attempt_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Map a 0–100 percentage score to a Level record ID if possible, 
     * or return the lowest level. (Can be customized)
     */
    public static function determineLevelId(int $percentage): ?int
    {
        $label = match(true) {
            $percentage >= 85 => 'B2',
            $percentage >= 70 => 'B1',
            $percentage >= 50 => 'A2',
            default           => 'A1',
        };

        $level = Level::where('name', 'like', "%{$label}%")->first()
            ?? Level::orderBy('order')->first();
            
        return $level?->id;
    }
    
    /**
     * Helper to get a nicely formatted level name based on the level relation.
     */
    public function getLevelName(): string
    {
        return $this->level ? $this->level->name : 'A1';
    }

    /**
     * Whether this session has completed the pretest step.
     */
    public function hasDonePretest(): bool
    {
        return in_array($this->status, [
            'pretest_done',
            'guidebook_done',
            'posttest_done',
            'completed',
        ]);
    }

    /**
     * Whether this session has completed the guidebook step.
     */
    public function hasDoneGuidebook(): bool
    {
        return in_array($this->status, [
            'guidebook_done',
            'posttest_done',
            'completed',
        ]);
    }

    /**
     * Whether this session has completed the posttest step.
     */
    public function hasDonePosttest(): bool
    {
        return in_array($this->status, [
            'posttest_done',
            'completed',
        ]);
    }

    /**
     * Resolves the correct route based on the current session status.
     */
    public function resolveLearningRoute(): string
    {
        return match($this->status) {
            'not_started'    => 'learning.pretest',
            'pretest_done'   => 'learning.guidebook',
            'guidebook_done' => 'learning.posttest',
            'posttest_done', 'completed' => 'learning.result',
            default          => 'learning.pretest',
        };
    }
}
