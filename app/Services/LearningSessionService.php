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
     * Finds the active session or creates a new one safely.
     * Generic version — used when resuming without a level context.
     */
    public function getOrCreateActiveSession(User $user): LearningSession
    {
        return DB::transaction(function () use ($user) {
            $session = LearningSession::where('user_id', $user->id)
                ->whereNotIn('status', ['completed'])
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $session) {
                $session = LearningSession::create([
                    'user_id' => $user->id,
                    'status'  => 'not_started',
                ]);
                Log::info("Created new learning session for user {$user->id}");
            }

            return $session;
        });
    }

    /**
     * Finds an active session for a specific level, or creates one.
     * Used when the user clicks a level card on the dashboard.
     */
    public function getOrCreateSessionForLevel(User $user, \App\Models\Level $level): LearningSession
    {
        return DB::transaction(function () use ($user, $level) {
            // Look for an in-progress session already linked to this level
            $session = LearningSession::where('user_id', $user->id)
                ->where('level_id', $level->id)
                ->whereNotIn('status', ['completed'])
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $session) {
                $session = LearningSession::create([
                    'user_id'  => $user->id,
                    'level_id' => $level->id,
                    'status'   => 'not_started',
                ]);
                Log::info("Created new learning session for user {$user->id} on level {$level->id}");
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
                ? (int) round(($correctCount / $attempt->total_questions) * 100) 
                : 0;
                
            $attempt->update([
                'score'       => $score,
                'passed'      => true,
                'finished_at' => now(),
            ]);

            // Update session status.
            // NOTE: level_id is intentionally NOT overridden here.
            // The user already selected their level via startLevel(); the pretest
            // is a knowledge check (not a placement test) for that specific level.
            $session->update([
                'status' => 'pretest_done',
            ]);

            Log::info("User {$user->id} completed pretest for level {$session->level_id}. Score: {$score}/{$attempt->total_questions}");
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
                ? (int) round(($correctCount / $attempt->total_questions) * 100) 
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
                'status'      => 'completed',
                'improvement' => $improvement,
            ]);

            // Only unlock next level if passed
            if ($isPassed) {
                $unlockService = app(\App\Services\LevelUnlockService::class);
                $unlockService->checkAndUnlockNextLevel($user, $attempt->lesson);
            }

            Log::info("User {$user->id} completed posttest. Passed: " . ($isPassed ? 'Yes' : 'No') . ". Improvement: {$improvement}%");
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

