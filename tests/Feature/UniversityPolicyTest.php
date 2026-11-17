<?php

use App\Models\University;
use App\Models\User;

test('admins can manage universities', function () {
    $admin = User::factory()->admin()->create();
    $university = University::factory()->create();

    expect($admin->can('viewAny', University::class))->toBeTrue()
        ->and($admin->can('view', $university))->toBeTrue()
        ->and($admin->can('create', University::class))->toBeTrue()
        ->and($admin->can('update', $university))->toBeTrue()
        ->and($admin->can('delete', $university))->toBeTrue();
});

test('students cannot manage universities', function () {
    $student = User::factory()->create();
    $university = University::factory()->create();

    expect($student->can('viewAny', University::class))->toBeFalse()
        ->and($student->can('view', $university))->toBeFalse()
        ->and($student->can('create', University::class))->toBeFalse()
        ->and($student->can('update', $university))->toBeFalse()
        ->and($student->can('delete', $university))->toBeFalse();
});
