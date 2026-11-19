<?php

use App\Enums\SubjectStatus;
use App\Models\Subject;

test('final grade above minimum marks the subject passed', function () {
    $subject = Subject::factory()->graded(15)->create();

    expect($subject->status)->toBe(SubjectStatus::Passed);
});

test('final grade below minimum marks the subject failed', function () {
    $subject = Subject::factory()->graded(8)->create();

    expect($subject->status)->toBe(SubjectStatus::Failed);
});

test('changing the final grade updates the status', function () {
    $subject = Subject::factory()->graded(15)->create();

    $subject->update(['final_grade' => 5]);

    expect($subject->fresh()->status)->toBe(SubjectStatus::Failed);
});

test('changing the minimum passing grade recomputes the status', function () {
    $subject = Subject::factory()->graded(12)->create();

    $subject->update(['min_passing_grade' => 15]);

    expect($subject->fresh()->status)->toBe(SubjectStatus::Failed);
});

test('status stays manual without a final grade', function () {
    $subject = Subject::factory()->create(['status' => SubjectStatus::InProgress]);

    $subject->update(['name' => 'Otro Nombre']);

    expect($subject->fresh()->status)->toBe(SubjectStatus::InProgress);
});
