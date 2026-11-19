<?php

use App\Models\Period;
use App\Models\Subject;
use App\Models\User;

test('owners can manage their own subjects', function () {
    $owner = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();

    expect($owner->can('viewAny', Subject::class))->toBeTrue()
        ->and($owner->can('create', Subject::class))->toBeTrue()
        ->and($owner->can('view', $subject))->toBeTrue()
        ->and($owner->can('update', $subject))->toBeTrue()
        ->and($owner->can('delete', $subject))->toBeTrue();
});

test('students cannot manage subjects of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();

    expect($intruder->can('view', $subject))->toBeFalse()
        ->and($intruder->can('update', $subject))->toBeFalse()
        ->and($intruder->can('delete', $subject))->toBeFalse();
});

test('admins cannot manage subjects of other users', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($owner)->create();
    $subject = Subject::factory()->for($period)->create();

    expect($admin->can('view', $subject))->toBeFalse()
        ->and($admin->can('update', $subject))->toBeFalse()
        ->and($admin->can('delete', $subject))->toBeFalse();
});
