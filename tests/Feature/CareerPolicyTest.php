<?php

use App\Models\Career;
use App\Models\User;

test('admins can manage careers', function () {
    $admin = User::factory()->admin()->create();
    $career = Career::factory()->create();

    expect($admin->can('viewAny', Career::class))->toBeTrue()
        ->and($admin->can('view', $career))->toBeTrue()
        ->and($admin->can('create', Career::class))->toBeTrue()
        ->and($admin->can('update', $career))->toBeTrue()
        ->and($admin->can('delete', $career))->toBeTrue();
});

test('students cannot manage careers', function () {
    $student = User::factory()->create();
    $career = Career::factory()->create();

    expect($student->can('viewAny', Career::class))->toBeFalse()
        ->and($student->can('view', $career))->toBeFalse()
        ->and($student->can('create', Career::class))->toBeFalse()
        ->and($student->can('update', $career))->toBeFalse()
        ->and($student->can('delete', $career))->toBeFalse();
});
