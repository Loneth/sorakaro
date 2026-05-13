<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\Attempt;
use App\Models\LearningSession;
use App\Services\LearningSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LearningController extends Controller
{
    private LearningSessionService $sessionService;

    public function __construct(LearningSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // ─── 1. Start ─────────────────────────────────────────────────────────────

    /**
     * Generic resume: if there is an active session, continue it.
     * If not, send the user back to the dashboard to choose a level.
     */
    public function start(Request $request)
    {
        $session = LearningSession::where('user_id', $request->user()->id)
            ->whereNotIn('status', ['completed'])
            ->latest()
            ->first();

        if (! $session) {
            return redirect()->route('dashboard')
                ->with('info', 'Pilih level yang ingin kamu pelajari.');
        }

        $route = $session->resolveLearningRoute();
        Log::info("Resuming learning session {$session->id}, route: {$route}");

        return redirect()->route($route);
    }

    /**
     * Level-specific entry: start or resume the guided flow for a chosen level.
     * This is the primary entry point triggered by the level cards on the dashboard.
     */
    public function startLevel(Request $request, Level $level)
    {
        // Guard: level must be unlocked for this user
        if (! $request->user()->hasUnlockedLevel($level)) {
            return redirect()->route('dashboard')
                ->with('error', 'Level ini belum terbuka. Selesaikan level sebelumnya terlebih dahulu.');
        }

        $session = $this->sessionService->getOrCreateSessionForLevel($request->user(), $level);

        $route = $session->resolveLearningRoute();
        Log::info("Starting level {$level->id} for user {$request->user()->id}, route: {$route}");

        return redirect()->route($route);
    }

    // ─── 2. Pretest ──────────────────────────────────────────────────────────

    public function pretest(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || $session->hasDonePretest()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        // Create an attempt if one doesn't exist yet
        if (! $session->pretest_attempt_id) {
            $lesson = $this->resolveAssessmentLesson($session->level_id, 'pretest');

            if (! $lesson) {
                return redirect()->route('dashboard')->with('error', 'Tidak ada soal pretest tersedia untuk level ini. Hubungi admin.');
            }

            $totalQuestions = min(10, $lesson->questions_count);

            $attempt = Attempt::create([
                'user_id'         => $request->user()->id,
                'lesson_id'       => $lesson->id,
                'score'           => 0,
                'total_questions' => $totalQuestions,
            ]);

            $session->update(['pretest_attempt_id' => $attempt->id]);
        }

        $attempt = $session->pretestAttempt;
        // Limit to the total questions we allocated for this attempt
        $questions = $attempt->lesson->questions()->inRandomOrder()->limit($attempt->total_questions)->get();

        return view('learning.pretest', compact('questions', 'session'));
    }

    public function submitPretest(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || $session->hasDonePretest()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        $attempt = $session->pretestAttempt;
        if (! $attempt) {
            return redirect()->route('learning.pretest');
        }

        $answers = $request->input('answers', []);
        
        // Delegate submission to service (includes transaction, logging, score, level determination)
        $this->sessionService->submitPretest($session, $attempt, $answers, $request->user());

        $levelName = $session->fresh()->getLevelName();

        return redirect()->route('learning.guidebook')
            ->with('success', 'Tes pemahaman awal selesai! Sekarang pelajari materinya. 📖');
    }

    // ─── 3. Guidebook ────────────────────────────────────────────────────────

    public function guidebook(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || ! $session->hasDonePretest()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        if ($session->hasDoneGuidebook()) {
            return redirect()->route($session->resolveLearningRoute());
        }

        $level    = $session->level ?? Level::orderBy('order')->first();
        $sections = $level?->guidebookSections()->with('items')->get() ?? collect();

        return view('learning.guidebook', compact('session', 'level', 'sections'));
    }

    public function completeGuidebook(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || $session->hasDoneGuidebook()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        $session->update(['status' => 'guidebook_done']);

        return redirect()->route('learning.posttest')
            ->with('success', 'Panduan selesai! Sekarang uji pemahaman kamu. 💪');
    }

    // ─── 4. Posttest ─────────────────────────────────────────────────────────

    public function posttest(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || ! $session->hasDoneGuidebook()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        if ($session->hasDonePosttest()) {
            return redirect()->route($session->resolveLearningRoute());
        }

        if (! $session->posttest_attempt_id) {
            $lesson = $this->resolveAssessmentLesson($session->level_id, 'posttest');

            if (! $lesson) {
                return redirect()->route('dashboard')->with('error', 'Tidak ada soal posttest tersedia untuk level ini. Hubungi admin.');
            }

            $totalQuestions = min(10, $lesson->questions_count);

            $attempt = Attempt::create([
                'user_id'         => $request->user()->id,
                'lesson_id'       => $lesson->id,
                'score'           => 0,
                'total_questions' => $totalQuestions,
            ]);

            $session->update(['posttest_attempt_id' => $attempt->id]);
        }

        $attempt = $session->posttestAttempt;
        $questions = $attempt->lesson->questions()->inRandomOrder()->limit($attempt->total_questions)->get();

        return view('learning.posttest', compact('questions', 'session'));
    }

    public function submitPosttest(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (! $session || $session->hasDonePosttest()) {
            return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start');
        }

        $attempt = $session->posttestAttempt;
        if (! $attempt) {
            return redirect()->route('learning.posttest');
        }

        $answers = $request->input('answers', []);
        
        $this->sessionService->submitPosttest($session, $attempt, $answers, $request->user());

        return redirect()->route('learning.result');
    }

    // ─── 5. Result ───────────────────────────────────────────────────────────

    public function result(Request $request)
    {
        // Try to get an active completed session, or the most recent one
        $session = LearningSession::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->with(['pretestAttempt', 'posttestAttempt', 'level'])
            ->latest()
            ->first();

        if (! $session) {
            return redirect()->route('learning.start');
        }

        return view('learning.result', compact('session'));
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Get the active (not completed) LearningSession for the user.
     */
    private function getActiveSession(Request $request): ?LearningSession
    {
        return LearningSession::where('user_id', $request->user()->id)
            ->whereNotIn('status', ['completed'])
            ->latest()
            ->first();
    }

    /**
     * Resolve the assessment lesson for a given level and type (pretest|posttest).
     *
     * Priority:
     *   1. Lesson with assessment_type = $type AND level_id = $levelId
     *   2. Lesson with is_assessment = true AND level_id = $levelId (legacy fallback)
     *   3. null — caller must handle missing lesson gracefully
     *
     * NOTE: No global fallback. Assessment MUST be contextual to the level.
     */
    private function resolveAssessmentLesson(int $levelId, string $type): ?\App\Models\Lesson
    {
        // Primary: new contextual assessment_type field
        $lesson = Lesson::where('level_id', $levelId)
            ->where('assessment_type', $type)
            ->withCount('questions')
            ->first();

        // Legacy fallback: old boolean is_assessment (only for backward compat)
        if (! $lesson) {
            $lesson = Lesson::where('level_id', $levelId)
                ->where('is_assessment', true)
                ->withCount('questions')
                ->first();

            if ($lesson) {
                Log::warning(
                    "Level {$levelId}: Using legacy is_assessment lesson #{$lesson->id} as {$type}. " .
                    "Please set assessment_type='{$type}' on this lesson in the admin panel."
                );
            }
        }

        // Reject if lesson has no questions
        if ($lesson && $lesson->questions_count === 0) {
            Log::error("Level {$levelId}: Assessment lesson #{$lesson->id} ({$type}) has 0 questions.");
            return null;
        }

        return $lesson;
    }
}
