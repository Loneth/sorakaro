<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Question;
use App\Models\Choice;
use App\Services\LevelUnlockService;
use Illuminate\Http\Request;

class LearnController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->unsetRelation('progress');
        $user->load('progress');
        $levels = Level::orderBy('order')->get();

        return view('learn.index', compact('levels', 'user'));
    }


    public function showLevel(Level $level)
    {
        $lessons = $level->lessons()
            ->withCount('questions')
            ->orderBy('order')
            ->get();

        $userId = auth()->id();

        $attemptsByLesson = Attempt::where('user_id', $userId)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('lesson_id');

        foreach ($lessons as $lesson) {
            $lessonAttempts = $attemptsByLesson->get($lesson->id, collect());

            $lesson->attempts_count = $lessonAttempts->count();
            $lesson->latest_attempt = $lessonAttempts->first();

            if ($lessonAttempts->isEmpty()) {
                $lesson->status = 'not_started';
            } elseif ($lesson->latest_attempt->finished_at === null) {
                $lesson->status = 'in_progress';
            } else {
                $lesson->status = 'completed';
            }
        }

        return view('learn.level', compact('level', 'lessons'));
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $currentLevel = null;
        if ($user->current_level_id) {
            $currentLevel = Level::find($user->current_level_id);
        }

        if (!$currentLevel) {
            $currentLevel = Level::orderBy('order')->first();

            if ($currentLevel && !$user->current_level_id) {
                $user->update(['current_level_id' => $currentLevel->id]);
            }
        }

        $lastUnfinished = Attempt::with(['lesson', 'lesson.level'])
            ->where('user_id', $user->id)
            ->whereNull('finished_at')
            ->latest('updated_at')
            ->first();

        return view('dashboard', compact('lastUnfinished', 'currentLevel'));
    }

    /**
     * Redirect to current level's guidebook
     */
    public function dashboardGuidebook(Request $request)
    {
        $user = $request->user();

        $currentLevel = null;
        if ($user->current_level_id) {
            $currentLevel = Level::find($user->current_level_id);
        }

        if (!$currentLevel) {
            $currentLevel = Level::orderBy('order')->first();
        }

        if ($currentLevel) {
            // ✅ learn.guidebook expects {level}
            return redirect()->route('learn.guidebook', ['level' => $currentLevel->id]);
        }

        return redirect()->route('dashboard')->with('error', 'No levels available yet.');
    }

    public function resume(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== $request->user()->id || $attempt->finished_at) {
            return redirect()->route('learn.index');
        }

        session()->put('attempt_id', $attempt->id);

        $answeredQIds = $attempt->answers()->pluck('question_id');

        $nextQuestion = $attempt->lesson->questions()
            ->whereNotIn('id', $answeredQIds)
            ->orderBy('order')
            ->orderBy('id')
            ->first();

        if (!$nextQuestion) {
            return redirect()->route('learn.result', [
                'lesson' => $attempt->lesson_id,
                'attempt' => $attempt->id,
            ]);
        }

        return redirect()->route('learn.question', [
            'lesson' => $attempt->lesson_id,
            'question' => $nextQuestion->id,
        ]);
    }

    public function start(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        $totalQuestions = $lesson->questions()->count();

        if ($totalQuestions === 0) {
            return redirect()->back()->with('error', 'Lesson ini belum punya soal.');
        }

        $attempt = Attempt::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'score' => 0,
            'total_questions' => $totalQuestions,
        ]);

        session()->put('attempt_id', $attempt->id);

        $firstQuestion = $lesson->questions()
            ->orderBy('order')
            ->orderBy('id')
            ->first();

        return redirect()->route('learn.question', [
            'lesson' => $lesson->id,
            'question' => $firstQuestion->id,
        ]);
    }

    public function showQuestion(Request $request, Lesson $lesson, Question $question)
    {
        abort_unless($question->lesson_id === $lesson->id, 404);

        $attemptId = session('attempt_id');
        if (!$attemptId) {
            return redirect()->route('learn.level', $lesson->level_id)
                ->with('error', 'Silakan start lesson terlebih dahulu.');
        }

        $attempt = Attempt::where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        // Load questions for navigation and progress
        $questions = $lesson->questions()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $currentIndex = $questions->search(fn ($q) => $q->id === $question->id);

        // If question not found in this lesson list
        if ($currentIndex === false) {
            abort(404);
        }

        $prevQuestion = $questions[$currentIndex - 1] ?? null;
        $nextQuestion = $questions[$currentIndex + 1] ?? null;

        $prevUrl = $prevQuestion
            ? route('learn.question', ['lesson' => $lesson->id, 'question' => $prevQuestion->id])
            : null;

        $nextUrl = $nextQuestion
            ? route('learn.question', ['lesson' => $lesson->id, 'question' => $nextQuestion->id])
            : route('learn.result', ['lesson' => $lesson->id, 'attempt' => $attempt->id]);

        $progress = [
            'current' => $currentIndex + 1,
            'total' => $questions->count(),
        ];

        $choices = $question->choices()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // Fetch all answers once (for answeredCount + current answer + potential UI use)
        $userAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->get()
            ->keyBy('question_id');

        $userAnswer = $userAnswers->get($question->id);
        $answeredCount = $userAnswers->count();

        return view('learn.question', compact(
            'lesson',
            'question',
            'questions',
            'choices',
            'attempt',
            'userAnswer',
            'userAnswers',
            'answeredCount',
            'prevUrl',
            'nextUrl',
            'progress'
        ));
    }

    public function submitAnswer(Request $request, Lesson $lesson, Question $question)
    {
        abort_unless($question->lesson_id === $lesson->id, 404);

        $attemptId = session('attempt_id');
        if (!$attemptId) {
            return redirect()->route('learn.index');
        }

        // ✅ Ensure attempt belongs to current user and lesson
        $attempt = Attempt::where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $validated = $request->validate([
            'choice_id' => ['required', 'integer', 'exists:choices,id'],
        ]);

        $choice = Choice::where('id', $validated['choice_id'])
            ->where('question_id', $question->id)
            ->firstOrFail();

        AttemptAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'choice_id' => $choice->id,
                'is_correct' => (bool) $choice->is_correct,
            ]
        );

        // Recalculate score (correct count)
        $score = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('is_correct', true)
            ->count();

        $attempt->update(['score' => $score]);

        $total = max(1, (int) $attempt->total_questions);
        $percentage = (int) round(($score / $total) * 100);

        // default pass rate kalau null
        $passRate = (int) ($lesson->pass_rate ?? 70);

        // hitung passed
        $passed = $percentage >= $passRate;

        // simpan status passed setiap submit (boleh), minimal saat last question
        $attempt->update(['passed' => $passed]);


        // Efficient last-question check: compare to last question id in lesson ordering
        $lastQuestionId = $lesson->questions()
            ->orderBy('order')
            ->orderBy('id')
            ->value('id'); // <-- this returns first, not last, so we must get last correctly

        // Correct way: get LAST id
        $lastQuestionId = $lesson->questions()
            ->orderBy('order', 'desc')
            ->orderBy('id', 'desc')
            ->value('id');

        $isLastQuestion = ((int) $lastQuestionId === (int) $question->id);

        if ($isLastQuestion) {
            $attempt->update(['finished_at' => now()]);

            // Unlock next level if applicable
            $unlockService = app(LevelUnlockService::class);
            $unlockService->checkAndUnlockNextLevel($request->user(), $lesson);
        }

        // Redirect back to show feedback
        return redirect()->route('learn.question', [
            'lesson' => $lesson->id,
            'question' => $question->id,
        ]);
    }

    public function result(Request $request, Lesson $lesson, Attempt $attempt)
    {
        abort_unless($attempt->lesson_id === $lesson->id, 404);
        abort_unless($attempt->user_id === $request->user()->id, 403);

        session()->forget('attempt_id');

        return view('learn.result', compact('lesson', 'attempt'));
    }
}
