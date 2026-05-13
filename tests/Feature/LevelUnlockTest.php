<?php

use App\Models\Level;
use App\Models\User;
use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Create 3 levels
    $this->level1 = Level::create(['name' => 'Level 1', 'order' => 1]);
    $this->level2 = Level::create(['name' => 'Level 2', 'order' => 2]);
    $this->level3 = Level::create(['name' => 'Level 3', 'order' => 3]);
});

test('first level is always unlocked', function () {
    expect($this->user->hasUnlockedLevel($this->level1))->toBeTrue();
});

test('subsequent levels are locked by default', function () {
    expect($this->user->hasUnlockedLevel($this->level2))->toBeFalse();
    expect($this->user->hasUnlockedLevel($this->level3))->toBeFalse();
});

test('level 2 unlocks when level 1 guided learning flow is completed', function () {
    LearningSession::create([
        'user_id' => $this->user->id,
        'level_id' => $this->level1->id,
        'status' => 'completed'
    ]);

    expect($this->user->hasUnlockedLevel($this->level1))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level2))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level3))->toBeFalse(); // level 3 still locked
});

test('level 3 unlocks when level 2 guided learning flow is completed', function () {
    LearningSession::create([
        'user_id' => $this->user->id,
        'level_id' => $this->level2->id,
        'status' => 'completed'
    ]);

    // If level 2 is completed, level 3 should be unlocked (and level 1 and 2 as well)
    expect($this->user->hasUnlockedLevel($this->level1))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level2))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level3))->toBeTrue();
});

test('fallback to legacy user progress still works', function () {
    \App\Models\UserProgress::create([
        'user_id' => $this->user->id,
        'highest_unlocked_level_id' => $this->level2->id
    ]);

    expect($this->user->hasUnlockedLevel($this->level1))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level2))->toBeTrue();
    expect($this->user->hasUnlockedLevel($this->level3))->toBeFalse();
});
