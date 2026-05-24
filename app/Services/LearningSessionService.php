<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\LearningSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LearningSessionService
{
    /**
     * Finds an existing active session (generic, no level context).
     * Used only for read-only resume scenarios — does NOT create sessions.
     *
     * NOTE: Creating a session without a level_id would produce a "ghost" session
     * that blocks the user. Always use getOrCreateSessionForLevel() instead.
     */
    public function getOrCreateActiveSession(User $user): ?LearningSession
    {
        return LearningSession::where('user_id', $user->id)
            ->whereIn('status', LearningSession::ACTIVE_STATUSES)
            ->whereNotNull('level_id')
            ->lockForUpdate()
            ->latest()
            ->first();
    }

    /**
     * Finds an active session for a specific level, or creates one.
     * Used when the user clicks a level card on the dashboard.
     *
     * Guarantees EXACTLY ONE active session per user per level.
     */
    public function getOrCreateSessionForLevel(User $user, \App\Models\Level $level): LearningSession
    {
        return DB::transaction(function () use ($user, $level) {
            // Look for an in-progress session already linked to this level
            $session = LearningSession::where('user_id', $user->id)
                ->where('level_id', $level->id)
                ->whereIn('status', LearningSession::ACTIVE_STATUSES)
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $session) {
                $session = LearningSession::create([
                    'user_id'  => $user->id,
                    'level_id' => $level->id,
                    'status'   => LearningSession::STATUS_NOT_STARTED,
                ]);
                Log::info("[LearningSessionService] Created new session#{$session->id} for user {$user->id} on level {$level->id}.");
            } else {
                Log::info("[LearningSessionService] Resumed existing session#{$session->id} for user {$user->id} on level {$level->id}: status='{$session->status}'.");
            }

            return $session;
        });
    }

    /**
     * Submits the pretest safely.
     */
    public function submitPretest(LearningSession $session, Attempt $attempt, array $answers, User $user): void
    {
        if ($attempt->finished_at !== null) {
            Log::warning("Double submission attempt on pretest by user {$user->id}");
            return;
        }

        DB::transaction(function () use ($session, $attempt, $answers, $user) {
            // Validate and save answers
            $this->validateAndSaveAnswers($attempt, $answers);

            // Calculate score as percentage
            $correctCount = AttemptAnswer::where('attempt_id', $attempt->id)
                ->where('is_correct', true)
                ->count();
                
            $score = $attempt->total_questions > 0 
                ? min(max((int) round(($correctCount / $attempt->total_questions) * 100), 0), 100)
                : 0;
                
            $attempt->update([
                'score'       => $score,
                'passed'      => true,
                'finished_at' => now(),
            ]);

            // Update session status to pretest_done.
            // NOTE: level_id is intentionally NOT overridden here.
            // The user already selected their level via startLevel(); the pretest
            // is a knowledge check (not a placement test) for that specific level.
            $session->update([
                'status' => LearningSession::STATUS_PRETEST_DONE,
            ]);

            Log::info("[LearningSessionService] User {$user->id} completed pretest for session#{$session->id} level {$session->level_id}. Score: {$score}/{$attempt->total_questions}");
        });
    }

    /**
     * Submits the posttest safely.
     */
    public function submitPosttest(LearningSession $session, Attempt $attempt, array $answers, User $user): void
    {
        if ($attempt->finished_at !== null) {
            Log::warning("Double submission attempt on posttest by user {$user->id}");
            return;
        }

        DB::transaction(function () use ($session, $attempt, $answers, $user) {
            // Validate and save answers
            $this->validateAndSaveAnswers($attempt, $answers);

            // Calculate score as percentage
            $correctCount = AttemptAnswer::where('attempt_id', $attempt->id)
                ->where('is_correct', true)
                ->count();
                
            $posttestScore = $attempt->total_questions > 0 
                ? min(max((int) round(($correctCount / $attempt->total_questions) * 100), 0), 100)
                : 0;

            $passRate = $attempt->lesson->pass_rate ?? 70;
            $isPassed = $posttestScore >= $passRate;

            $attempt->update([
                'score'       => $posttestScore,
                'passed'      => $isPassed,
                'finished_at' => now(),
            ]);

            // Calculate pretest percentage for improvement comparison safely
            $pretest = $session->pretestAttempt;
            $pretestScore = $pretest ? $pretest->score : 0;

            // Raw percentage difference instead of division
            $improvement = $posttestScore - $pretestScore;

            $session->update([
                'status'      => LearningSession::STATUS_COMPLETED,
                'improvement' => $improvement,
            ]);

            // Only unlock next level if passed
            if ($isPassed) {
                $unlockService = app(\App\Services\LevelUnlockService::class);
                $unlockService->checkAndUnlockNextLevel($user, $attempt->lesson);
            }

            Log::info("[LearningSessionService] User {$user->id} completed session#{$session->id}. Passed: " . ($isPassed ? 'Yes' : 'No') . ". Improvement: {$improvement}%");
        });
    }

    /**
     * Strictly validates answers to prevent tampering, then saves them.
     *
     * Supports two answer modes:
     *   MCQ     → $answers[$questionId] = $choiceId  (integer/string)
     *   Writing → $answers[$questionId] = $textAnswer (string, not an integer choice id)
     *
     * The distinction is made by checking Question::isWritingType() and whether
     * the submitted value matches any existing choice ID (guards against spoofing).
     */
    private function validateAndSaveAnswers(Attempt $attempt, array $answers): void
    {
        $questionIds = array_keys($answers);

        // Fetch questions strictly belonging to this attempt's lesson
        $questions = Question::whereIn('id', $questionIds)
            ->where('lesson_id', $attempt->lesson_id)
            ->with('choices')
            ->get()
            ->keyBy('id');

        foreach ($answers as $questionId => $submittedValue) {
            $question = $questions->get($questionId);

            if (! $question) {
                Log::warning("Attempt {$attempt->id}: Question {$questionId} does not belong to lesson {$attempt->lesson_id}");
                continue;
            }

            // ── Writing / Typing question ────────────────────────────────────
            if ($question->isWritingType()) {
                $textAnswer = (string) $submittedValue;
                $isCorrect  = $question->isCorrectTextAnswer($textAnswer);

                AttemptAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'choice_id'   => null,
                        'text_answer' => $textAnswer,
                        'is_correct'  => $isCorrect,
                    ]
                );
                continue;
            }

            // ── MCQ question ─────────────────────────────────────────────────
            $choiceId = $submittedValue;
            $choice   = $question->choices->firstWhere('id', $choiceId);

            if (! $choice) {
                Log::warning("Attempt {$attempt->id}: Choice {$choiceId} does not belong to question {$questionId}");
                continue;
            }

            AttemptAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                [
                    'choice_id'   => $choiceId,
                    'text_answer' => null,
                    'is_correct'  => $choice->is_correct,
                ]
            );
        }
    }
}

