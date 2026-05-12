You are a senior Laravel engineer performing a production hardening pass on an existing guided learning system.

## 🎯 Objective

Make the guided learning flow robust, safe, and production-ready by addressing edge cases, data integrity, concurrency issues, and defensive programming.

---

## 📌 Context

The system already has:

* LearningSession model
* Attempt-based quiz system
* Pretest & Posttest using assessment lessons
* resolveLearningRoute() implemented
* No PHP session dependency

---

## ⚠️ CRITICAL TASKS

---

# 1. Prevent Multiple Active Learning Sessions

Ensure only ONE active session per user.

### Requirements:

* Before creating a new session:

  * check existing session where status != 'completed'
* If exists:

  * redirect to resolveLearningRoute()

### Add DB-level safety:

* optional unique constraint:
  (user_id, status != completed) → enforce at application level

---

# 2. Attempt Locking (Prevent Double Submission)

In both pretest and posttest submission:

### Add:

```php
if ($attempt->finished_at) {
    return redirect()->route('learning.result');
}
```

### Also:

* wrap submission in DB transaction
* prevent duplicate AttemptAnswer entries

---

# 3. Transaction Safety

Wrap critical flows in DB transactions:

* submitPretest()
* submitPosttest()

```php
DB::transaction(function () {
   // save answers
   // calculate score
   // update attempt
   // update learning session
});
```

---

# 4. Improvement Calculation Safety

Handle edge cases:

* division by zero
* missing attempts

### Safe formula:

```php
$improvement = ($pretestScore > 0)
    ? round((($posttestScore - $pretestScore) / $pretestScore) * 100)
    : 0;
```

---

# 5. Sync User Level

After pretest:

* update:

```php
$user->current_level_id = $learningSession->level_id;
$user->save();
```

Ensure consistency between:

* LearningSession
* User
* Guidebook

---

# 6. Strict Flow Enforcement (Middleware)

Enhance EnsureLearningStep:

### Must validate:

* pretest_attempt_id exists
* posttest_attempt_id exists
* session status matches route

### If invalid:

* redirect using resolveLearningRoute()

---

# 7. Data Validation (Critical)

Before saving answers:

* validate:

  * question belongs to lesson
  * choice belongs to question

Reject invalid payloads.

---

# 8. Prevent Question Tampering

Ensure:

* user cannot submit answers for questions not in the current assessment set

### Optional:

* store question IDs in LearningSession (JSON column)
  OR
* validate against lesson_id strictly

---

# 9. Analytics Protection

Ensure ALL analytics queries exclude assessment:

```php
->whereHas('lesson', fn($q) => $q->where('is_assessment', false))
```

Apply to:

* XP
* leaderboard
* streak
* dashboard stats

---

# 10. Resume Safety

If user leaves mid-flow:

* always fetch session from DB
* route via resolveLearningRoute()

No reliance on client state.

---

# 11. Logging & Monitoring

Add logs for:

* session creation
* submission
* abnormal states (missing attempt, invalid step)

Use:

```php
Log::warning(...)
```

---

# 12. Graceful Failure Handling

Handle cases:

* no assessment lesson found
* empty question set
* missing attempt

Return:

* user-friendly message
* redirect safely (NOT crash)

---

# 13. Performance Optimization (Quick Wins)

* eager load relationships:

```php
Lesson::with('questions.choices')
```

* avoid N+1 queries
* cache level lookup if needed

---

# 14. Security Hardening

* ensure CSRF protection on all forms
* authorize access:

  * user can only access their own LearningSession
* validate all request inputs

---

# 15. Code Quality

* move logic into services:

  * LevelPlacementService
  * LearningSessionService
* keep controllers thin

---

## ✅ Deliverables

* Hardened controllers
* Improved middleware
* Transaction-safe submissions
* Validation layer added
* Logging added
* No edge case crashes
* Production-safe flow

---

## ⚠️ Important

* Do NOT rewrite architecture
* Focus only on safety and robustness
* Keep changes minimal but impactful

---

Think step-by-step:

1. Identify risks
2. Fix them
3. Keep system stable
