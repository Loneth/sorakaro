<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'age',
        'current_level_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Avatar icon based on gender
     */
    public function getAvatarIcon(): string
    {
        return match ($this->gender) {
            'male' => '🧑🏻‍💼',
            'female' => '👩🏻‍💼',
            default => '👤',
        };
    }

    /**
     * User's current level (users.current_level_id)
     */
    public function currentLevel()
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    /**
     * Progress record (user_progress)
     */
    public function progress()
    {
        return $this->hasOne(UserProgress::class);
    }

    /**
     * Attempts
     */
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * Check if a level is unlocked for this user.
     * New architecture: Unlocked if user has a completed LearningSession
     * for the *previous* level (based on order).
     * Fallback to legacy user_progress logic.
     */
    public function hasUnlockedLevel(Level $level): bool
    {
        // 1. The first level is always unlocked
        $firstLevel = Level::orderBy('order')->first();
        if (!$firstLevel || (int) $level->id === (int) $firstLevel->id) {
            return true;
        }

        // 2. Determine what the "previous" level is
        $previousLevel = Level::where('order', '<', $level->order)
            ->orderByDesc('order')
            ->first();

        // 3. Check guided learning flow completion
        if ($previousLevel) {
            // Unlocked if they completed the previous level (or any higher level)
            $hasGuidedCompletion = \App\Models\LearningSession::where('user_id', $this->id)
                ->where('status', 'completed')
                ->whereHas('level', function($query) use ($previousLevel) {
                    $query->where('order', '>=', $previousLevel->order);
                })
                ->exists();

            if ($hasGuidedCompletion) {
                return true;
            }
        }

        // 4. Legacy fallback (UserProgress)
        $highestId = \App\Models\UserProgress::where('user_id', $this->id)
            ->value('highest_unlocked_level_id');

        if ($highestId) {
            $highestOrder = Level::where('id', $highestId)->value('order') ?? 1;
            if ((int) $level->order <= (int) $highestOrder) {
                return true;
            }
        }

        return false;
    }

    /**
     * Highest unlocked order (helper)
     */
    public function getHighestUnlockedOrder(): int
    {
        // Get the highest order from completed guided sessions
        $highestGuidedOrder = Level::whereHas('learningSessions', function ($query) {
            $query->where('user_id', $this->id)
                  ->where('status', 'completed');
        })->max('order');

        // The user is unlocked up to (highest completed order + 1)
        // If they completed order 1, they have unlocked order 2.
        $guidedUnlockOrder = $highestGuidedOrder ? ($highestGuidedOrder + 1) : 1;

        // Legacy check
        $legacyHighestOrder = 1;
        $progress = $this->progress;
        if ($progress && $progress->highest_unlocked_level_id) {
            $legacyHighestOrder = Level::where('id', $progress->highest_unlocked_level_id)->value('order') ?? 1;
        }

        return max((int) $guidedUnlockOrder, (int) $legacyHighestOrder);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
