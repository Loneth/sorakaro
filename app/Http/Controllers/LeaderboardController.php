<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard rankings.
     */
    public function index(Request $request)
    {
        $range = $request->query('range', 'weekly');
        $limit = 20;

        // Determine date filter based on range
        // Default 'weekly' is last 7 days. 'all' is no filter.
        $dateFilter = $range === 'weekly' ? now()->subDays(7) : null;

        // Query Builder with Aggregation Joins
        // Structure: attempts -> users -> lessons (filtered by posttest)
        
        $query = DB::table('attempts')
            ->join('users', 'attempts.user_id', '=', 'users.id')
            ->join('lessons', 'attempts.lesson_id', '=', 'lessons.id')
            ->where('lessons.assessment_type', 'posttest')
            ->select([
                'users.id',
                'users.name',
                DB::raw('AVG(attempts.score) as avg_posttest_score'),
                DB::raw('COUNT(DISTINCT CASE WHEN attempts.passed = 1 THEN lessons.level_id END) as completed_levels')
            ]);

        // Apply Date Filter
        if ($dateFilter) {
            $query->where('attempts.created_at', '>=', $dateFilter);
        }

        // Apply Group By and limit
        $leaderboard = $query->groupBy('users.id', 'users.name')
            ->limit($limit)
            ->get();

        // Eager load attempt dates to prevent N+1 query when calculating streaks
        $userIds = $leaderboard->pluck('id');
        $allAttemptDates = DB::table('attempts')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('DATE(created_at) as attempt_date'))
            ->groupBy('user_id', 'attempt_date')
            ->orderByDesc('attempt_date')
            ->get()
            ->groupBy('user_id');

        // Calculate streak and format data
        $leaderboard->transform(function ($item) use ($allAttemptDates) {
            $item->avg_posttest_score = $item->avg_posttest_score !== null ? round($item->avg_posttest_score) : null;
            
            // Calculate streak for this user
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
        $leaderboard = $leaderboard->sortBy([
            ['completed_levels', 'desc'],
            ['avg_posttest_score', 'desc'],
            ['streak', 'desc'],
        ])->values();

        return view('leaderboard.index', [
            'leaderboard' => $leaderboard,
            'range' => $range
        ]);
    }
}
