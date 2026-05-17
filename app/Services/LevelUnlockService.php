<?php

namespace App\Services;

use App\Models\User;
use App\Models\Level;
use App\Models\Lesson;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LevelUnlockService
{
    /**
     * Check and unlock next level if current level is completed
     * Called after an attempt is finished
     */
    public function checkAndUnlockNextLevel(User $user, Lesson $lesson): void
    {
        $currentLevel = $lesson->level;
        
        // Check if user has completed all lessons in this level
        if (!$this->hasCompletedLevel($user, $currentLevel)) {
            return;
        }

        // Get next level
        $nextLevel = Level::where('order', '>', $currentLevel->order)
        ->orderBy('order')
        ->first();
        
        if (!$nextLevel) {
            // No next level exists (user completed final level)
            Log::info("User {$user->id} completed final level {$currentLevel->id}");
            return;
        }

        // Unlock next level
        $this->unlockLevel($user, $nextLevel);
    }

    public function hasCompletedLevel(User $user, Level $level): bool
    {
        // Level is considered completed only if its posttest is passed
        return DB::table('attempts')
            ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('attempts.user_id', $user->id)
            ->where('lessons.level_id', $level->id)
            ->where('lessons.assessment_type', 'posttest')
            ->whereNotNull('attempts.finished_at')
            ->where('attempts.passed', true)
            ->exists();
    }

    /**
     * Unlock a level for a user
     */
    public function unlockLevel(User $user, Level $level): void
    {
        $progress = $user->progress;

        // Create progress record if it doesn't exist
        if (!$progress) {
            UserProgress::create([
                'user_id' => $user->id,
                'current_level_id' => $level->id,
                'highest_unlocked_level_id' => $level->id,
            ]);

            // Optional sync users table
            $user->update(['current_level_id' => $level->id]);

            Log::info("Created progress for user {$user->id}, unlocked level {$level->id}");
            return;
        }

        // ✅ Compare using raw ID (no relation dependency)
        $currentHighest = Level::find($progress->highest_unlocked_level_id);

        // If highest is missing (corrupt), treat as lowest
        $currentHighestOrder = $currentHighest?->order ?? 0;

        // Update only if new level is higher
        if ($level->order > $currentHighestOrder) {
            $progress->update([
                'highest_unlocked_level_id' => $level->id,
                'current_level_id' => $level->id,
            ]);

            // Optional sync users table
            $user->update(['current_level_id' => $level->id]);

            Log::info("User {$user->id} unlocked level {$level->id} ({$level->name})");
        }
    }



    /**
     * Initialize progress for a new user (set to level 1)
     */
    public function initializeUserProgress(User $user): UserProgress
    {
        $firstLevel = Level::orderBy('order')->first();

        if (!$firstLevel) {
            throw new \Exception('No levels found in database');
        }

        return UserProgress::create([
            'user_id' => $user->id,
            'current_level_id' => $firstLevel->id,
            'highest_unlocked_level_id' => $firstLevel->id,
        ]);
    }

    /**
     * Backfill progress for existing users
     */
    public function backfillUserProgress(User $user): UserProgress
    {
        // Check existing attempts to determine highest completed level
        $highestCompletedLevel = $this->getHighestCompletedLevel($user);
        
        if (!$highestCompletedLevel) {
            // No completed levels, initialize to level 1
            return $this->initializeUserProgress($user);
        }

        // Unlock up to and including next level after highest completed
        $nextLevel = Level::where('order', $highestCompletedLevel->order + 1)->first();
        $highestUnlocked = $nextLevel ?? $highestCompletedLevel;

        return UserProgress::create([
            'user_id' => $user->id,
            'current_level_id' => $highestUnlocked->id,
            'highest_unlocked_level_id' => $highestUnlocked->id,
        ]);
    }

    /**
     * Get the highest level that user has fully completed
     */
    private function getHighestCompletedLevel(User $user): ?Level
    {
        $levels = Level::with('lessons')->orderBy('order')->get();

        $highestCompleted = null;

        foreach ($levels as $level) {
            if ($this->hasCompletedLevel($user, $level)) {
                $highestCompleted = $level;
            } else {
                // Stop at first incomplete level
                break;
            }
        }

        return $highestCompleted;
    }
}
