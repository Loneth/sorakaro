<?php

use App\Models\Level;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Create Level 1
    $this->level1 = Level::create(['name' => 'Level 1', 'order' => 1]);
    
    $this->level1Pretest = Lesson::create([
        'level_id' => $this->level1->id,
        'title' => 'Pretest Level 1',
        'assessment_type' => 'pretest'
    ]);
    Question::create(['lesson_id' => $this->level1Pretest->id, 'prompt' => 'Q1', 'type' => 'mcq']);

    $this->level1Posttest = Lesson::create([
        'level_id' => $this->level1->id,
        'title' => 'Posttest Level 1',
        'assessment_type' => 'posttest'
    ]);
    Question::create(['lesson_id' => $this->level1Posttest->id, 'prompt' => 'Q2', 'type' => 'mcq']);

    // Create Level 2
    $this->level2 = Level::create(['name' => 'Level 2', 'order' => 2]);
    
    $this->level2Pretest = Lesson::create([
        'level_id' => $this->level2->id,
        'title' => 'Pretest Level 2',
        'assessment_type' => 'pretest'
    ]);
    Question::create(['lesson_id' => $this->level2Pretest->id, 'prompt' => 'Q3', 'type' => 'mcq']);

    $this->level2Posttest = Lesson::create([
        'level_id' => $this->level2->id,
        'title' => 'Posttest Level 2',
        'is_assessment' => true, // Legacy flag test
        'assessment_type' => null,
    ]);
    Question::create(['lesson_id' => $this->level2Posttest->id, 'prompt' => 'Q4', 'type' => 'mcq']);
});

test('level 1 pretest is correctly contextual', function () {
    $session = LearningSession::create([
        'user_id' => $this->user->id,
        'level_id' => $this->level1->id,
        'status' => 'not_started'
    ]);

    $response = $this->actingAs($this->user)->get(route('learning.pretest'));
    $response->assertStatus(200);

    $session->refresh();
    expect($session->pretestAttempt->lesson_id)->toBe($this->level1Pretest->id);
});

test('level 2 pretest does not fetch level 1 pretest (strict contextual)', function () {
    // Ensure the user has access to Level 2 (just update their current_level_id if it's used as a guard elsewhere, though pretest controller just checks the active session's level_id)
    $this->user->update(['current_level_id' => $this->level2->id]);

    $session = LearningSession::create([
        'user_id' => $this->user->id,
        'level_id' => $this->level2->id,
        'status' => 'not_started'
    ]);

    $response = $this->actingAs($this->user)->get(route('learning.pretest'));
    $response->assertStatus(200);

    $session->refresh();
    // Must be level 2's pretest, not level 1's
    expect($session->pretestAttempt->lesson_id)->toBe($this->level2Pretest->id);
});

test('posttest properly falls back to legacy is_assessment flag', function () {
    // Create a dummy attempt to satisfy the foreign key constraint
    $dummyAttempt = \App\Models\Attempt::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->level2Pretest->id,
        'score' => 10,
        'total_questions' => 10,
    ]);

    // Satisfy middleware requirements: status = guidebook_done, pretest_attempt_id = real id
    $session = LearningSession::create([
        'user_id' => $this->user->id,
        'level_id' => $this->level2->id,
        'status' => 'guidebook_done',
        'pretest_attempt_id' => $dummyAttempt->id 
    ]);

    $response = $this->actingAs($this->user)->get(route('learning.posttest'));
    $response->assertStatus(200);

    $session->refresh();
    // Must fall back correctly to the legacy lesson for Level 2
    expect($session->posttestAttempt->lesson_id)->toBe($this->level2Posttest->id);
});
