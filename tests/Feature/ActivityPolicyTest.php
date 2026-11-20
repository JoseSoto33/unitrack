<?php

use App\Models\Activity;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;

test('owners can manage their own activities', function () {
    $owner = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create();

    expect($owner->can('viewAny', Activity::class))->toBeTrue()
        ->and($owner->can('create', Activity::class))->toBeTrue()
        ->and($owner->can('view', $activity))->toBeTrue()
        ->and($owner->can('update', $activity))->toBeTrue()
        ->and($owner->can('delete', $activity))->toBeTrue();
});

test('students cannot manage activities of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create();

    expect($intruder->can('view', $activity))->toBeFalse()
        ->and($intruder->can('update', $activity))->toBeFalse()
        ->and($intruder->can('delete', $activity))->toBeFalse();
});

test('admins cannot manage activities of other users', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create();

    expect($admin->can('view', $activity))->toBeFalse()
        ->and($admin->can('update', $activity))->toBeFalse()
        ->and($admin->can('delete', $activity))->toBeFalse();
});
