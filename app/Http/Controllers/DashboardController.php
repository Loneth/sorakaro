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
                    'icon' => '<svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>',
                    'date' => $attempt->finished_at,
                    'time_ago' => $attempt->finished_at->diffForHumans(),
                ]);
            } else {
                $activities->push([
                    'type' => $attempt->passed ? 'posttest_passed' : 'posttest_failed',
                    'title' => "Posttest {$levelName} — " . ($attempt->passed ? 'Lulus' : 'Coba Lagi'),
                    'icon' => $attempt->passed 
                        ? '<svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>' 
                        : '<svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
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
                'icon' => '<svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>',
                'date' => $session->updated_at,
                'time_ago' => $session->updated_at->diffForHumans(),
            ]);
        }

        $recentActivities = $activities->sortByDesc('date')->take(5)->values()->toArray();

        // 5. Leaderboard Top 3 (Weekly)
        $sevenDaysAgo = now()->subDays(7);
        $leaderboardData = DB::table('attempts')
            ->join('users', 'attempts.user_id', '=', 'users.id')
            ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('lessons.assessment_type', 'posttest')
            ->where('attempts.created_at', '>=', $sevenDaysAgo)
            ->select([
                'users.id',
                'users.name',
                DB::raw('AVG(attempts.score) as avg_posttest_score'),
                DB::raw('COUNT(DISTINCT CASE WHEN attempts.passed = 1 THEN lessons.level_id END) as completed_levels')
            ])
            ->groupBy('users.id', 'users.name')
            ->get();

        // Eager load attempt dates to prevent N+1 query
        $userIds = $leaderboardData->pluck('id');
        $allAttemptDates = DB::table('attempts')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('DATE(created_at) as attempt_date'))
            ->groupBy('user_id', 'attempt_date')
            ->orderByDesc('attempt_date')
            ->get()
            ->groupBy('user_id');

        // Calculate streak and format data
        $leaderboardData->transform(function ($item) use ($allAttemptDates) {
            // Check for empty state, though the query filters for completed_levels > 0
            $item->avg_posttest_score = $item->avg_posttest_score !== null ? round($item->avg_posttest_score) : null;
            
            // Calculate streak
            $attemptDates = collect();
            if ($allAttemptDates->has($item->id)) {
                $attemptDates = $allAttemptDates[$item->id]->pluck('attempt_date')->map(fn($d) => \Carbon\Carbon::parse($d));
            }

            $streak = 0;
            if ($attemptDates->isNotEmpty()) {
                $streak = 1;
                $today = now()->startOfDay();
                $firstDate = $attemptDates->first();
                
                if ($firstDate->gte($today->copy()->subDay())) {
                    for ($i = 0; $i < $attemptDates->count() - 1; $i++) {
                        if ($attemptDates[$i]->diffInDays($attemptDates[$i + 1]) === 1) {
                            $streak++;
                        } else {
                            break;
                        }
                    }
                }
            }
            $item->streak = $streak;

            return $item;
        });

        // Sort by primary: completed levels, secondary: avg score, tertiary: streak
        $leaderboardData = $leaderboardData->sortBy([
            ['completed_levels', 'desc'],
            ['avg_posttest_score', 'desc'],
            ['streak', 'desc'],
        ])->values();

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

        // 6. Total Assessments Passed — replacing XP for a mastery-focused metric
        $totalAssessmentsPassed = DB::table('attempts')
            ->where('user_id', $userId)
            ->where('passed', true)
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
            ->whereNotNull('level_id')
            ->with('level')
            ->latest()
            ->first();

        // 11. Category Performance
        $categoryStats = DB::table('attempt_answers')
            ->join('attempts', 'attempts.id', '=', 'attempt_answers.attempt_id')
            ->join('questions', 'questions.id', '=', 'attempt_answers.question_id')
            ->where('attempts.user_id', $userId)
            ->whereNotNull('questions.skill_category')
            ->select(
                'questions.skill_category',
                DB::raw('SUM(attempt_answers.is_correct) as total_correct'),
                DB::raw('COUNT(attempt_answers.id) as total_answered')
            )
            ->groupBy('questions.skill_category')
            ->get()
            ->map(function ($stat) {
                $percentage = $stat->total_answered > 0 ? (int) round(($stat->total_correct / $stat->total_answered) * 100) : 0;
                
                // Visual states mapping
                if ($percentage < 40) {
                    $state = 'weak';
                    $color = 'bg-red-500';
                    $bg = 'bg-red-50';
                    $textColor = 'text-red-600';
                } elseif ($percentage < 70) {
                    $state = 'improving';
                    $color = 'bg-yellow-400';
                    $bg = 'bg-yellow-50';
                    $textColor = 'text-yellow-600';
                } else {
                    $state = 'mastered';
                    $color = 'bg-green-500';
                    $bg = 'bg-green-50';
                    $textColor = 'text-green-600';
                }

                $catIcons = [
                    'greetings'    => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'conversation' => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                    'grammar'      => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                    'numbers'      => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>',
                    'listening'    => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>',
                    'writing'      => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>',
                    'vocabulary'   => '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>',
                ];
                $displayIcon = $catIcons[$stat->skill_category] ?? '<svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>';
                $displayLabel = \App\Models\Question::SKILL_CATEGORIES[$stat->skill_category] ?? $stat->skill_category;

                return [
                    'category' => $displayLabel,
                    'percentage' => $percentage,
                    'state' => $state,
                    'color' => $color,
                    'bg' => $bg,
                    'text_color' => $textColor,
                    'icon' => $displayIcon,
                ];
            })
            ->sortByDesc('percentage')
            ->values();

        $performanceInsight = null;
        if ($categoryStats->count() > 0) {
            $weakest = $categoryStats->last();
            $best = $categoryStats->first();
            
            if ($weakest['percentage'] < 40) {
                $performanceInsight = "Kamu masih lemah di {$weakest['category']} {$weakest['icon']} Yuk tingkatkan lagi!";
            } elseif ($best['percentage'] >= 70) {
                $performanceInsight = "Kategori terbaikmu: {$best['category']} 🎉 Terus pertahankan!";
            }
        }

        // 12. Smart CTA
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
            'totalAssessmentsPassed',
            'dailyStreak',
            'streakHistory',
            'levelsCompleted',
            'totalLevels',
            'overallProgress',
            'nextLesson',
            'activeSession',
            'smartCTA',
            'categoryStats',
            'performanceInsight',
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
