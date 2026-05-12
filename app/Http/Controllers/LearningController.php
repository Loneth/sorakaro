<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\Attempt;
use App\Models\LearningSession;
use App\Services\LearningSessionService;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    private LearningSessionService $sessionService;

    public function __construct(LearningSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // ─── 1. Start ─────────────────────────────────────────────────────────────

    public function start(Request $request)
    {
        $session = $this->sessionService->getOrCreateActiveSession($request->user());

        $route = $session->resolveLearningRoute();
        \Illuminate\Support\Facades\Log::info('Learning route: ' . $route);

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
            $lesson = Lesson::where('is_assessment', true)->withCount('questions')->inRandomOrder()->first();
            
            if (! $lesson || $lesson->questions_count == 0) {
                return redirect()->route('dashboard')->with('error', 'Tidak ada materi tersedia untuk pre-test.');
            }

            $totalQuestions = min(10, $lesson->questions_count);

            $attempt = Attempt::create([
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
                'score' => 0,
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
            ->with('success', "Level terdeteksi: {$levelName}. Sekarang baca panduan materi! 🚀");
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
            $lesson = Lesson::where('is_assessment', true)->withCount('questions')->inRandomOrder()->first();
            
            if (! $lesson || $lesson->questions_count == 0) {
                return redirect()->route('dashboard')->with('error', 'Tidak ada materi tersedia untuk post-test.');
            }

            $totalQuestions = min(10, $lesson->questions_count);

            $attempt = Attempt::create([
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
                'score' => 0,
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
}
