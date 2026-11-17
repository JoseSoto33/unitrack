<?php

use App\Models\Career;
use App\Models\User;

test('users can be enrolled in careers with pivot data', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create();

    $career->users()->attach($user, [
        'status' => 'in_progress',
        'started_at' => '2026-01-15',
        'finished_at' => null,
    ]);

    $enrolled = $user->careers()->first();

    expect($enrolled)->not->toBeNull()
        ->and($enrolled->id)->toBe($career->id)
        ->and($enrolled->pivot->status)->toBe('in_progress')
        ->and($enrolled->pivot->started_at)->toBe('2026-01-15')
        ->and($career->users()->first()->id)->toBe($user->id);
});

test('enrollment status defaults to in progress', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create();

    $career->users()->attach($user);

    expect($user->careers()->first()->pivot->status)->toBe('in_progress');
});
