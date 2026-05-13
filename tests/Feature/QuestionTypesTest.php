<?php

use App\Models\Question;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\User;
use App\Models\Attempt;
use App\Models\Choice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('isWritingType correctly identifies writing and typing', function () {
    $mcq = new Question(['type' => 'mcq']);
    $writing = new Question(['type' => 'writing']);
    $typing = new Question(['type' => 'typing']); // legacy

    expect($mcq->isWritingType())->toBeFalse();
    expect($writing->isWritingType())->toBeTrue();
    expect($typing->isWritingType())->toBeTrue();
});

test('hasAudio correctly identifies audio presence', function () {
    $noAudio = new Question(['audio_path' => null]);
    $hasAudio = new Question(['audio_path' => 'audio/hello.mp3']);

    expect($noAudio->hasAudio())->toBeFalse();
    expect($hasAudio->hasAudio())->toBeTrue();
});

test('isCorrectTextAnswer correctly validates using accepted_answers array', function () {
    $question = new Question([
        'type' => 'writing',
        'accepted_answers' => ['halo', 'hai', 'horas']
    ]);
    
    // Case insensitivity & whitespace
    expect($question->isCorrectTextAnswer('halo'))->toBeTrue();
    expect($question->isCorrectTextAnswer('  HALO  '))->toBeTrue();
    expect($question->isCorrectTextAnswer('Horas'))->toBeTrue();
    
    // Incorrect answers
    expect($question->isCorrectTextAnswer('hello'))->toBeFalse();
    expect($question->isCorrectTextAnswer(''))->toBeFalse();
});

test('isCorrectTextAnswer falls back to correct choice if accepted_answers is empty', function () {
    $level = Level::create(['name' => 'L1', 'order' => 1]);
    $lesson = Lesson::create(['title' => 'Test', 'level_id' => $level->id]);
    $question = Question::create([
        'lesson_id' => $lesson->id,
        'type' => 'writing',
        'prompt' => 'Test',
    ]);
    Choice::create(['question_id' => $question->id, 'text' => 'Mejuah-juah', 'is_correct' => true]);
    Choice::create(['question_id' => $question->id, 'text' => 'Salah', 'is_correct' => false]);
    
    $question->refresh(); // load choices
    
    expect($question->isCorrectTextAnswer('mejuah-juah'))->toBeTrue();
    expect($question->isCorrectTextAnswer('  MEJUAH-JUAH  '))->toBeTrue();
    expect($question->isCorrectTextAnswer('salah'))->toBeFalse();
});

test('validateAndSaveAnswers correctly handles both mcq and writing questions', function () {
    $user = User::factory()->create();
    $level = Level::create(['name' => 'L1', 'order' => 1]);
    $lesson = Lesson::create(['title' => 'Test', 'level_id' => $level->id]);
    
    // Create MCQ Question
    $mcq = Question::create(['lesson_id' => $lesson->id, 'type' => 'mcq', 'prompt' => 'MCQ']);
    $correctChoice = Choice::create(['question_id' => $mcq->id, 'text' => 'Benar', 'is_correct' => true]);
    $wrongChoice = Choice::create(['question_id' => $mcq->id, 'text' => 'Salah', 'is_correct' => false]);

    // Create Writing Question
    $writing = Question::create([
        'lesson_id' => $lesson->id, 
        'type' => 'writing', 
        'prompt' => 'Writing',
        'accepted_answers' => ['halo']
    ]);

    $attempt = Attempt::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);

    $service = app(\App\Services\LearningSessionService::class);
    
    // Simulate user submit
    // Note: validateAndSaveAnswers is private. We can test it by using Reflection or by calling submitPretest/submitPosttest.
    // Instead of Reflection, let's just make validateAndSaveAnswers protected or use submitPosttest.
    // Actually, I'll use Reflection method for unit testing the private method directly.
    $method = new ReflectionMethod(\App\Services\LearningSessionService::class, 'validateAndSaveAnswers');
    $method->setAccessible(true);
    
    $method->invokeArgs($service, [
        $attempt, 
        [
            $mcq->id => $correctChoice->id, // MCQ answer
            $writing->id => '  HALO  '      // Writing answer
        ]
    ]);

    // Assert MCQ answer
    $mcqAnswer = \App\Models\AttemptAnswer::where('attempt_id', $attempt->id)->where('question_id', $mcq->id)->first();
    expect($mcqAnswer->choice_id)->toBe($correctChoice->id);
    expect($mcqAnswer->text_answer)->toBeNull();
    expect($mcqAnswer->is_correct)->toBe(1);

    // Assert Writing answer
    $writingAnswer = \App\Models\AttemptAnswer::where('attempt_id', $attempt->id)->where('question_id', $writing->id)->first();
    expect($writingAnswer->choice_id)->toBeNull();
    expect($writingAnswer->text_answer)->toBe('  HALO  ');
    expect($writingAnswer->is_correct)->toBe(1);
});
