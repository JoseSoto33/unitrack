<?php

use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;

test('owners can manage their own schedules', function () {
    $owner = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();

    expect($owner->can('viewAny', Schedule::class))->toBeTrue()
        ->and($owner->can('create', Schedule::class))->toBeTrue()
        ->and($owner->can('view', $schedule))->toBeTrue()
        ->and($owner->can('update', $schedule))->toBeTrue()
        ->and($owner->can('delete', $schedule))->toBeTrue();
});

test('students cannot manage schedules of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();

    expect($intruder->can('view', $schedule))->toBeFalse()
        ->and($intruder->can('update', $schedule))->toBeFalse()
        ->and($intruder->can('delete', $schedule))->toBeFalse();
});

test('admins cannot manage schedules of other users', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();

    expect($admin->can('view', $schedule))->toBeFalse()
        ->and($admin->can('update', $schedule))->toBeFalse()
        ->and($admin->can('delete', $schedule))->toBeFalse();
});
