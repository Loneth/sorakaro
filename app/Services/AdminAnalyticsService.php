<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\GuidebookItem;
use App\Models\LearningSession;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticsService
{
    // =========================================================================
    // SECTION 1: CORE METRICS
    // =========================================================================

    public function getTotalUsers(): int
    {
        // Exclude admins if you want purely students, but for now just count users
        // Assuming roles are checked elsewhere, but standard User count is fine
        return User::count();
    }

    public function getActiveUsersToday(): int
    {
        $today = Carbon::today();

        // Users who made an attempt today
        $attemptingUsers = Attempt::whereDate('created_at', $today)
            ->distinct('user_id')
            ->pluck('user_id');

        // Users who updated their learning session today
        $learningUsers = LearningSession::whereDate('updated_at', $today)
            ->distinct('user_id')
            ->pluck('user_id');

        return $attemptingUsers->merge($learningUsers)->unique()->count();
    }

    public function getTotalCompletedLevels(): int
    {
        // Based on completed posttests that signify level completion
        return Attempt::join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('lessons.assessment_type', 'posttest')
            ->where('attempts.passed', true)
            ->count();
    }

    public function getTotalCompletedPosttests(): int
    {
        return Attempt::join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('lessons.assessment_type', 'posttest')
            ->whereNotNull('attempts.finished_at')
            ->count();
    }

    public function getGlobalAveragePosttestScore(): float
    {
        $avg = Attempt::join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('lessons.assessment_type', 'posttest')
            ->whereNotNull('attempts.finished_at')
            ->avg('attempts.score');

        return $avg ? round($avg, 1) : 0.0;
    }

    // =========================================================================
    // SECTION 2: LEARNING ANALYTICS
    // =========================================================================

    public function getLevelPerformance(): array
    {
        // Calculate pass rate and avg score for posttests per level
        $stats = DB::table('attempts')
            ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->join('levels', 'lessons.level_id', '=', 'levels.id')
            ->where('lessons.assessment_type', 'posttest')
            ->whereNotNull('attempts.finished_at')
            ->select(
                'levels.id',
                'levels.name as level_name',
                DB::raw('COUNT(attempts.id) as total_attempts'),
                DB::raw('SUM(CASE WHEN attempts.passed = 1 THEN 1 ELSE 0 END) as passed_attempts'),
                DB::raw('AVG(attempts.score) as avg_score')
            )
            ->groupBy('levels.id', 'levels.name', 'levels.order')
            ->orderBy('levels.order')
            ->get();

        return $stats->map(function ($stat) {
            $passRate = $stat->total_attempts > 0
                ? round(($stat->passed_attempts / $stat->total_attempts) * 100, 1)
                : 0;

            return [
                'level' => $stat->level_name,
                'pass_rate' => $passRate,
                'avg_score' => round($stat->avg_score, 1),
            ];
        })->toArray();
    }


    public function getMostFailedQuestions(int $limit = 10): array
    {
        $stats = DB::table('attempt_answers')
            ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
            ->join('lessons', 'questions.lesson_id', '=', 'lessons.id')
            ->join('levels', 'lessons.level_id', '=', 'levels.id')
            ->select(
                'questions.id',
                'questions.prompt',
                'questions.type',
                'levels.name as level_name',
                DB::raw('COUNT(attempt_answers.id) as total_attempts'),
                DB::raw('SUM(CASE WHEN attempt_answers.is_correct = 0 THEN 1 ELSE 0 END) as failed_attempts')
            )
            ->groupBy('questions.id', 'questions.prompt', 'questions.type', 'levels.name')
            ->having('total_attempts', '>=', 5) // At least 5 attempts for statistical relevance
            ->orderByDesc(DB::raw('SUM(CASE WHEN attempt_answers.is_correct = 0 THEN 1 ELSE 0 END) / COUNT(attempt_answers.id)'))
            ->limit($limit)
            ->get();

        return $stats->map(function ($stat) {
            $failureRate = $stat->total_attempts > 0
                ? round(($stat->failed_attempts / $stat->total_attempts) * 100, 1)
                : 0;

            return [
                'id' => $stat->id,
                'prompt' => strip_tags($stat->prompt),
                'type' => $stat->type,
                'level' => $stat->level_name,
                'failure_rate' => $failureRate,
                'total_attempts' => $stat->total_attempts,
            ];
        })->toArray();
    }

    // =========================================================================
    // SECTION 3: GUIDEBOOK EFFECTIVENESS
    // =========================================================================

    public function getGuidebookImprovementRates(): array
    {
        // Use LearningSession improvement logic
        $stats = DB::table('learning_sessions')
            ->join('levels', 'learning_sessions.level_id', '=', 'levels.id')
            ->join('attempts as pretest', 'learning_sessions.pretest_attempt_id', '=', 'pretest.id')
            ->join('attempts as posttest', 'learning_sessions.posttest_attempt_id', '=', 'posttest.id')
            ->whereIn('learning_sessions.status', ['posttest_done', 'completed'])
            ->select(
                'levels.name as level_name',
                DB::raw('AVG(pretest.score) as avg_pretest'),
                DB::raw('AVG(posttest.score) as avg_posttest')
            )
            ->groupBy('levels.name', 'levels.order')
            ->orderBy('levels.order')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'level' => $stat->level_name,
                'pretest_avg' => round($stat->avg_pretest, 1),
                'posttest_avg' => round($stat->avg_posttest, 1),
                'improvement' => round($stat->avg_posttest - $stat->avg_pretest, 1),
            ];
        })->toArray();
    }

    // =========================================================================
    // SECTION 4: USER ENGAGEMENT
    // =========================================================================

    public function getAverageLearningStreak(): float
    {
        $avg = DB::table('users')->avg('streak');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function getDailyLearningActivity(int $days = 7): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        
        // Count finished attempts per day
        $activity = DB::table('attempts')
            ->select(DB::raw('DATE(finished_at) as date'), DB::raw('COUNT(id) as count'))
            ->where('finished_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(finished_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $data[] = isset($activity[$date]) ? $activity[$date]->count : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function getDropOffAnalytics(): array
    {
        $totalSessions = LearningSession::count();
        if ($totalSessions === 0) {
            return [
                'started' => 0,
                'pretest_done' => 0,
                'guidebook_done' => 0,
                'posttest_done' => 0,
            ];
        }

        $pretestDone = LearningSession::whereIn('status', ['pretest_done', 'guidebook_done', 'posttest_done', 'completed'])->count();
        $guidebookDone = LearningSession::whereIn('status', ['guidebook_done', 'posttest_done', 'completed'])->count();
        $posttestDone = LearningSession::whereIn('status', ['posttest_done', 'completed'])->count();

        return [
            'started' => 100, // 100% of sessions
            'pretest_done' => round(($pretestDone / $totalSessions) * 100, 1),
            'guidebook_done' => round(($guidebookDone / $totalSessions) * 100, 1),
            'posttest_done' => round(($posttestDone / $totalSessions) * 100, 1),
        ];
    }

    // =========================================================================
    // SECTION 5: CONTENT HEALTH
    // =========================================================================

    public function getContentHealthStats(): array
    {
        return [
            'total_levels' => Level::count(),
            'total_questions' => Question::count(),
            'total_listening' => Question::whereNotNull('audio_path')->count(),
            'total_writing' => Question::whereIn('type', [Question::TYPE_WRITING, Question::TYPE_TYPING])->count(),
            'total_image' => Question::whereNotNull('image_path')->count(),
            'total_guidebook_items' => GuidebookItem::count(),
        ];
    }
}
