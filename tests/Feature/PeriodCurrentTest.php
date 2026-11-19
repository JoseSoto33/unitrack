<?php

use App\Models\Career;
use App\Models\Period;
use App\Models\User;

test('marking a period current unsets the previous current of the same career', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create();
    $previous = Period::factory()->for($user)->for($career)->current()->create();
    $new = Period::factory()->for($user)->for($career)->current()->create();

    expect($previous->fresh()->is_current)->toBeFalse()
        ->and($new->fresh()->is_current)->toBeTrue();
});

test('current periods of other careers or users are not affected', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create();
    $otherCareerPeriod = Period::factory()->for($user)->current()->create();
    $otherUserPeriod = Period::factory()->for($career)->current()->create();

    Period::factory()->for($user)->for($career)->current()->create();

    expect($otherCareerPeriod->fresh()->is_current)->toBeTrue()
        ->and($otherUserPeriod->fresh()->is_current)->toBeTrue();
});

test('updating the current period keeps it current', function () {
    $period = Period::factory()->current()->create();

    $period->update(['number' => 2]);

    expect($period->fresh()->is_current)->toBeTrue();
});
