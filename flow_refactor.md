You are a senior full-stack engineer working on an existing production web app called Sorakaro, a gamified language learning platform.

🎯 Objective

Refactor the current learning flow into a guided, linear learning journey:

New Flow:
Dashboard → Start Learning → Pretest → Guidebook → Post-test → Result

📌 Current Flow (Existing)

Dashboard → Learn → Level चयन → Lesson → Questions → Result

This flow is too complex and requires manual navigation. We want to simplify and guide the user automatically.

🧩 Requirements

1. Replace Learn Flow Entry
   Remove or deprecate /learn, /learn/level, /lesson/\*
   Add primary CTA on Dashboard:
   Button: "Start Learning"
   Route: /learning/start
2. Create New Route Structure

Implement the following routes:

/learning/start → initialize session
/learning/pretest
/learning/guidebook
/learning/posttest
/learning/result

Ensure these routes are state-driven and sequential (user cannot skip steps).

3. Learning Session State (Core Logic)

Create a new entity:

LearningSession {
id: string
userId: string
status: 'not_started' | 'pretest_done' | 'guidebook_done' | 'posttest_done' | 'completed'
pretestScore: number
posttestScore: number
level: string
improvement: number
createdAt: Date
} 4. Pretest Logic
Generate adaptive/random questions
Store answers and calculate score
After submission:
determine user level (e.g., A1, A2, B1)

update session:

status = 'pretest_done'
pretestScore = X
level = computedLevel
Redirect → /learning/guidebook 5. Guidebook
Load content based on session.level
Track completion (simple: scroll to bottom OR click "Continue")
Update:
status = 'guidebook_done'
Redirect → /learning/posttest 6. Post-test
Similar structure to pretest but different questions
After submission:
calculate score
compute improvement:
improvement = posttestScore - pretestScore
status = 'completed'
Redirect → /learning/result 7. Result Page

Display:

Pretest vs Post-test score
Improvement %
Final level

Add CTA:

"Continue Learning"
"Retry" 8. Access Guard / Middleware

Prevent step skipping:

If user tries to access /learning/posttest before pretest → redirect back
Use middleware or server-side guard 9. Backward Compatibility
Do NOT delete old lesson system yet
Mark it as deprecated
Keep database intact 10. UI/UX Guidelines
Use a stepper at the top:
Pretest → Guide → Post-test → Result
Keep UI minimal and consistent
Use single primary color (blue) across the flow
Add progress indicators and motivational copy:
“You’re improving 🚀”
“Level detected: B1” 11. Tech Constraints
Follow existing project structure
Reuse components where possible
Keep API RESTful or follow existing backend pattern
Ensure all state changes are persisted
✅ Deliverables
Updated routes
New LearningSession model
Refactored flow logic
Middleware for step validation
Clean, maintainable code
⚠️ Important
Do NOT overengineer
Focus on clarity and user flow
Keep it production-ready

Think step-by-step before coding and explain your approach briefly before implementation.
