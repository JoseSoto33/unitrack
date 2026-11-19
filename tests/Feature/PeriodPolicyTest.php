<?php

use App\Models\Period;
use App\Models\User;

test('owners can manage their own periods', function () {
    $owner = User::factory()->create();
    $period = Period::factory()->for($owner)->create();

    expect($owner->can('viewAny', Period::class))->toBeTrue()
        ->and($owner->can('create', Period::class))->toBeTrue()
        ->and($owner->can('view', $period))->toBeTrue()
        ->and($owner->can('update', $period))->toBeTrue()
        ->and($owner->can('delete', $period))->toBeTrue();
});

test('students cannot manage periods of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $period = Period::factory()->for($owner)->create();

    expect($intruder->can('view', $period))->toBeFalse()
        ->and($intruder->can('update', $period))->toBeFalse()
        ->and($intruder->can('delete', $period))->toBeFalse();
});

test('admins cannot manage periods of other users', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($owner)->create();

    expect($admin->can('view', $period))->toBeFalse()
        ->and($admin->can('update', $period))->toBeFalse()
        ->and($admin->can('delete', $period))->toBeFalse();
});
