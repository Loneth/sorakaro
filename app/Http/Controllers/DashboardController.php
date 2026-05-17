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
        $user = $request->user();
        $userId = $user->id;

        // Base query for non-assessment attempts
        $baseAttemptQuery = fn() => Attempt::where('user_id', $userId)
            ->whereHas('lesson', fn($q) => $q);

        // 1. Calculate Learning Progress
        $totalQuestionsAnswered = (int) DB::table('attempt_answers')
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->where('attempts.user_id', $userId)
            ->count();

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

        // 4. Recent Activities
        $activities = collect();

        $recentFinishedAttempts = $baseAttemptQuery()
            ->with(['lesson.level'])
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->take(5)
            ->get();
            
        foreach ($recentFinishedAttempts as $attempt) {
            $levelName = $attempt->lesson->level->name ?? 'Level';
            $isPretest = $attempt->lesson->assessment_type === 'pretest';
            
            if ($isPretest) {
                $activities->push([
                    'type' => 'pretest',
                    'title' => "Pretest {$levelName} — Selesai",
                    'icon' => '📝',
                    'date' => $attempt->finished_at,
                    'time_ago' => $attempt->finished_at->diffForHumans(),
                ]);
            } else {
                $activities->push([
                    'type' => $attempt->passed ? 'posttest_passed' : 'posttest_failed',
                    'title' => "Posttest {$levelName} — " . ($attempt->passed ? 'Lulus' : 'Coba Lagi'),
                    'icon' => $attempt->passed ? '✅' : '❌',
                    'date' => $attempt->finished_at,
                    'time_ago' => $attempt->finished_at->diffForHumans(),
                ]);
            }
        }

        $recentSessions = LearningSession::where('user_id', $userId)
            ->whereIn('status', ['guidebook_done', 'completed'])
            ->with('level')
            ->latest('updated_at')
            ->take(5)
            ->get();
            
        foreach ($recentSessions as $session) {
            $levelName = $session->level->name ?? 'Level';
            $activities->push([
                'type' => 'guidebook',
                'title' => "Membaca Guidebook {$levelName}",
                'icon' => '📖',
                'date' => $session->updated_at,
                'time_ago' => $session->updated_at->diffForHumans(),
            ]);
        }

        $recentActivities = $activities->sortByDesc('date')->take(5)->values()->toArray();

        // 5. Leaderboard Top 3 (Weekly)
        $sevenDaysAgo = now()->subDays(7);
        $leaderboardData = DB::table('attempt_answers')
            ->join('attempts', 'attempt_answers.attempt_id', '=', 'attempts.id')
            ->join('users', 'attempts.user_id', '=', 'users.id')
            ->join('lessons', 'lessons.id', '=', 'attempts.lesson_id')
            ->where('attempts.created_at', '>=', $sevenDaysAgo)
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

        // 6. Total XP — sum of all correct answers ever
        $totalXP = (int) DB::table('attempt_answers')
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->join('lessons', 'lessons.id', '=', 'attempts.lesson_id')
            ->where('attempts.user_id', $userId)
            ->where('attempt_answers.is_correct', true)
            ->count();

        // 7. Daily Streak — consecutive days with at least one attempt
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

        $streakHistory = [];
        $daysId = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $hasActivity = $attemptDates->contains(fn($d) => $d->startOfDay()->equalTo($date));
            $streakHistory[] = [
                'day' => $daysId[$date->dayOfWeekIso],
                'active' => $hasActivity
            ];
        }

        // 8. Levels Completed (Mastery is determined by passing posttest)
        $levelsCompleted = (int) DB::table('attempts')
            ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('attempts.user_id', $userId)
            ->where('lessons.assessment_type', 'posttest')
            ->where('attempts.passed', true)
            ->whereNotNull('attempts.finished_at')
            ->distinct('lessons.level_id')
            ->count('lessons.level_id');
            
        $totalLevels = Level::count();
        $overallProgress = $totalLevels > 0 ? (int) round(($levelsCompleted / $totalLevels) * 100) : 0;

        // 9. Next Lesson — unused now, removing for clarity
        $nextLesson = null;

        // 10. Active LearningSession (any non-completed)
        $activeSession = LearningSession::where('user_id', $userId)
            ->whereNotIn('status', ['completed'])
            ->with('level')
            ->latest()
            ->first();

        // 11. Smart CTA
        $smartCTA = $this->resolveDashboardCTA($user, $activeSession, $levelsCompleted, $totalLevels);

        // 12. Level cards with unlock + completion state
        $user->load('progress');
        $allLevels  = Level::orderBy('order')->get();

        $levelCards = $allLevels->map(function (Level $level) use ($user, $userId, $activeSession) {
            $isUnlocked = $user->hasUnlockedLevel($level);

            $isCompleted = (bool) DB::table('attempts')
                ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
                ->where('attempts.user_id', $userId)
                ->where('lessons.level_id', $level->id)
                ->where('lessons.assessment_type', 'posttest')
                ->where('attempts.passed', true)
                ->whereNotNull('attempts.finished_at')
                ->exists();

            $progressPct = $isCompleted ? 100 : 0;

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
                'progress_pct'     => $progressPct,
            ];
        });

        // 13. Return View
        return view('dashboard', compact(
            'totalQuestionsAnswered',
            'currentLevel',
            'lastUnfinished',
            'recentActivities',
            'topLeaderboard',
            'myRank',
            'totalXP',
            'dailyStreak',
            'streakHistory',
            'levelsCompleted',
            'totalLevels',
            'overallProgress',
            'nextLesson',
            'activeSession',
            'smartCTA',
            'levelCards'
        ));
    }

    /**
     * Resolves contextual CTA based on user state
     */
    private function resolveDashboardCTA($user, $activeSession, $levelsCompleted, $totalLevels)
    {
        // State 4: Menyelesaikan semua level
        if ($levelsCompleted > 0 && $levelsCompleted >= $totalLevels) {
            return [
                'label'   => 'Review Materi',
                'subtext' => 'Kamu sudah menyelesaikan semua level 🎉',
                'route'   => route('learn.index'),
                'state'   => 'completed_all'
            ];
        }

        // State 2: Sedang belajar (active session)
        if ($activeSession) {
            $levelName = $activeSession->level->name ?? 'Level';
            return [
                'label'   => 'Lanjutkan Belajar',
                'subtext' => "Lanjutkan {$levelName}",
                'route'   => route('learning.start'), // will auto-resume active session
                'state'   => 'learning'
            ];
        }

        // State 3: Gagal posttest terakhir
        $lastCompletedSession = LearningSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['posttestAttempt', 'level'])
            ->latest()
            ->first();

        if ($lastCompletedSession && $lastCompletedSession->posttestAttempt && !$lastCompletedSession->posttestAttempt->passed) {
            $levelName = $lastCompletedSession->level->name ?? 'Level';
            return [
                'label'   => 'Coba Lagi',
                'subtext' => "Yuk selesaikan {$levelName}",
                'route'   => route('learning.start.level', $lastCompletedSession->level_id),
                'state'   => 'failed'
            ];
        }

        // State 1: Belum pernah belajar / default
        return [
            'label'   => 'Mulai Belajar',
            'subtext' => 'Mulai perjalanan belajar Bahasa Karo',
            'route'   => route('learn.index'),
            'state'   => 'start'
        ];
    }
}
