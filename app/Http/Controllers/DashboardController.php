<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Level;
use App\Models\Lesson;
use App\Models\LearningSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real user statistics.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Base query for non-assessment attempts
        $baseAttemptQuery = fn() => Attempt::where('user_id', $userId)
            ->whereHas('lesson', fn($q) => $q->where('is_assessment', false));

        // 1. Calculate KPIs
        $totalAttempts = $baseAttemptQuery()->count();
        
        // Average Score (0 if no attempts)
        $avgScore = (int) round($baseAttemptQuery()->avg('score') ?? 0);
        
        // Pass Rate
        $passedAttempts = $baseAttemptQuery()->where('passed', true)->count();
        $passRate = $totalAttempts > 0 
            ? (int) round(($passedAttempts / $totalAttempts) * 100) 
            : 0;

        // 2. Get Current Level
        $currentLevel = $baseAttemptQuery()
            ->with('lesson.level')
            ->latest()
            ->first()
            ?->lesson
            ?->level;

        // 3. Last Unfinished Attempt
        $lastUnfinished = $baseAttemptQuery()
            ->with('lesson.level')
            ->whereNull('finished_at')
            ->latest()
            ->first();

        // 4. Recent Attempts
        $recentAttempts = $baseAttemptQuery()
            ->with(['lesson.level'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'lesson_id' => $attempt->lesson_id,
                    'lesson' => $attempt->lesson->title ?? $attempt->lesson->name ?? 'Unknown Lesson',
                    'score' => (int) ($attempt->score ?? 0),
                    'passed' => (bool) $attempt->passed,
                    'date' => $attempt->created_at ? $attempt->created_at->diffForHumans() : '-',
                ];
            })
            ->toArray();

        // 5. Category Performance
        $categoryPerformance = DB::table('attempt_answers')
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->join('lessons', 'lessons.id', '=', 'attempts.lesson_id')
            ->where('attempts.user_id', $userId)
            ->where('lessons.is_assessment', false)
            ->select(
                'lessons.title as name',
                DB::raw('COUNT(*) as total_answers'),
                DB::raw('SUM(CASE WHEN attempt_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
            )
            ->groupBy('lessons.id', 'lessons.title')
            ->orderByDesc('total_answers')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $total = $row->total_answers;
                $correct = $row->correct_answers;
                $percent = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
                
                return [
                    'name' => $row->name,
                    'percent' => $percent,
                    'meta' => "{$correct}/{$total} correct",
                ];
            })
            ->toArray();

        // 6. Leaderboard Top 3 (Weekly)
        $sevenDaysAgo = now()->subDays(7);
        $leaderboardData = DB::table('attempt_answers')
            ->join('attempts', 'attempt_answers.attempt_id', '=', 'attempts.id')
            ->join('users', 'attempts.user_id', '=', 'users.id')
            ->join('lessons', 'lessons.id', '=', 'attempts.lesson_id')
            ->where('attempts.created_at', '>=', $sevenDaysAgo)
            ->where('lessons.is_assessment', false)
            ->select([
                'users.id',
                'users.name',
                DB::raw('SUM(attempt_answers.is_correct) as total_correct'),
                DB::raw('COUNT(DISTINCT attempts.id) as total_attempts'),
                DB::raw('COUNT(DISTINCT CASE WHEN attempts.passed = 1 THEN attempts.id END) as passed_attempts'),
                DB::raw('AVG(attempts.score) as avg_score')
            ])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_correct')
            ->orderByRaw('(COUNT(DISTINCT CASE WHEN attempts.passed = 1 THEN attempts.id END) / COUNT(DISTINCT attempts.id)) DESC')
            ->orderByDesc('avg_score')
            ->orderByDesc('total_attempts')
            ->get();

        $topLeaderboard = $leaderboardData->take(3)->map(function ($user) use ($userId) {
            $user->is_me = $user->id === $userId;
            return $user;
        });

        $myRank = null;
        $myPosition = $leaderboardData->search(fn($u) => $u->id === $userId);
        if ($myPosition !== false) {
            $myRank = $myPosition + 1;
        }

        // ═══════════════════════════════════════════════════════════
        // NEW: Gamification & Progress Data
        // ═══════════════════════════════════════════════════════════

        // 7. Total XP — sum of all correct answers ever
        $totalXP = (int) DB::table('attempt_answers')
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->join('lessons', 'lessons.id', '=', 'attempts.lesson_id')
            ->where('attempts.user_id', $userId)
            ->where('lessons.is_assessment', false)
            ->where('attempt_answers.is_correct', true)
            ->count();

        // 8. Daily Streak — consecutive days with at least one attempt
        $dailyStreak = 0;
        $attemptDates = $baseAttemptQuery()
            ->select(DB::raw('DATE(created_at) as attempt_date'))
            ->groupBy('attempt_date')
            ->orderByDesc('attempt_date')
            ->pluck('attempt_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        if ($attemptDates->isNotEmpty()) {
            $dailyStreak = 1;
            $today = now()->startOfDay();
            $firstDate = $attemptDates->first();
            
            // Only count streak if the most recent activity is today or yesterday
            if ($firstDate->gte($today->copy()->subDay())) {
                for ($i = 0; $i < $attemptDates->count() - 1; $i++) {
                    $current = $attemptDates[$i];
                    $next = $attemptDates[$i + 1];
                    if ($current->diffInDays($next) === 1) {
                        $dailyStreak++;
                    } else {
                        break;
                    }
                }
            } else {
                $dailyStreak = 0; // streak broken
            }
        }

        // 9. Lessons Completed — distinct lessons with at least one passed attempt
        $lessonsCompleted = $baseAttemptQuery()
            ->where('passed', true)
            ->distinct('lesson_id')
            ->count('lesson_id');

        // 10. Next Lesson — first lesson user hasn't passed yet (if no unfinished)
        $nextLesson = null;
        if (!$lastUnfinished) {
            $passedLessonIds = $baseAttemptQuery()
                ->where('passed', true)
                ->pluck('lesson_id')
                ->unique();

            $nextLesson = Lesson::where('is_assessment', false)
                ->whereNotIn('id', $passedLessonIds)
                ->orderBy('level_id')
                ->orderBy('order')
                ->first();
        }

        // 11. Active LearningSession (any non-completed)
        $activeSession = LearningSession::where('user_id', $userId)
            ->whereNotIn('status', ['completed'])
            ->with('level')
            ->latest()
            ->first();

        // 12. Hero CTA state
        // 'none'      → no active session, pick a level
        // 'active'    → session in progress for a specific level
        $heroCTAState = $activeSession ? 'active' : 'none';

        // 13. Level cards with unlock + completion state
        $user       = $request->user();
        $user->load('progress');
        $allLevels  = Level::orderBy('order')->get();

        $levelCards = $allLevels->map(function (Level $level) use ($user, $userId, $activeSession) {
            $isUnlocked = $user->hasUnlockedLevel($level);

            // Count non-assessment lessons in this level
            $totalLessons = $level->lessons()->where('is_assessment', false)->count();

            // Count lessons the user has passed at least once
            $passedLessons = 0;
            if ($isUnlocked && $totalLessons > 0) {
                $passedLessons = (int) DB::table('attempts')
                    ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
                    ->where('attempts.user_id', $userId)
                    ->where('lessons.level_id', $level->id)
                    ->where('lessons.is_assessment', false)
                    ->where('attempts.passed', true)
                    ->whereNotNull('attempts.finished_at')
                    ->distinct('attempts.lesson_id')
                    ->count('attempts.lesson_id');
            }

            $progressPct = $totalLessons > 0
                ? (int) round(($passedLessons / $totalLessons) * 100)
                : 0;

            $isCompleted = $isUnlocked && $progressPct >= 100;

            // Does the active session belong to this level?
            $hasActiveSession = $activeSession && (int) $activeSession->level_id === (int) $level->id;

            // Completed learning sessions for this level
            $completedSession = LearningSession::where('user_id', $userId)
                ->where('level_id', $level->id)
                ->where('status', 'completed')
                ->latest()
                ->first();

            return [
                'level'            => $level,
                'is_unlocked'      => $isUnlocked,
                'is_completed'     => $isCompleted,
                'has_active_session' => $hasActiveSession,
                'active_session'   => $hasActiveSession ? $activeSession : null,
                'completed_session' => $completedSession,
                'total_lessons'    => $totalLessons,
                'passed_lessons'   => $passedLessons,
                'progress_pct'     => $progressPct,
            ];
        });

        // 14. Return View
        return view('dashboard', compact(
            'totalAttempts',
            'avgScore',
            'passRate',
            'currentLevel',
            'lastUnfinished',
            'recentAttempts',
            'categoryPerformance',
            'topLeaderboard',
            'myRank',
            'totalXP',
            'dailyStreak',
            'lessonsCompleted',
            'nextLesson',
            'activeSession',
            'heroCTAState',
            'levelCards'
        ));
    }
}
