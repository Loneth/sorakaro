<?php

namespace App\Http\Middleware;

use App\Models\LearningSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents users from skipping steps in the guided learning flow.
 *
 * Guards:
 *   /learning/guidebook  → requires pretest_done
 *   /learning/posttest   → requires guidebook_done
 *   /learning/result     → requires posttest_done / completed
 */
class EnsureLearningStep
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        /** @var LearningSession|null $session */
        $session = LearningSession::where('user_id', $user->id)
            ->whereNotIn('status', ['completed'])
            ->latest()
            ->first();

        $routeName = $request->route()?->getName();

        // ── Guidebook: need pretest_done ──────────────────────────────────────
        if ($routeName === 'learning.guidebook') {
            if (! $session || ! $session->hasDonePretest() || ! $session->pretest_attempt_id) {
                return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start')
                    ->with('error', 'Selesaikan pretest terlebih dahulu.');
            }
        }

        // ── Posttest: need guidebook_done ─────────────────────────────────────
        if ($routeName === 'learning.posttest') {
            if (! $session || ! $session->hasDoneGuidebook() || ! $session->pretest_attempt_id) {
                return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start')
                    ->with('error', 'Baca panduan terlebih dahulu.');
            }
        }

        // ── Result: need posttest_done or completed ───────────────────────────
        if ($routeName === 'learning.result') {
            if (! $session || ! $session->hasDonePosttest() || ! $session->posttest_attempt_id) {
                // Allow if session is explicitly completed
                $completedSession = LearningSession::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->latest()
                    ->first();

                if (! $completedSession || ! $completedSession->posttest_attempt_id) {
                    return redirect()->route($session ? $session->resolveLearningRoute() : 'learning.start')
                        ->with('error', 'Selesaikan post-test terlebih dahulu.');
                }
            }
        }

        return $next($request);
    }
}
