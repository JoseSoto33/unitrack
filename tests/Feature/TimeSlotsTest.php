<?php

use App\Support\TimeSlots;

test('time slots cover the day in quarter hour steps', function () {
    $values = TimeSlots::values();

    expect($values)->toHaveCount(96)
        ->and($values)->toContain('00:00', '00:15', '00:30', '00:45', '07:00', '13:45', '23:45')
        ->and($values)->not->toContain('07:10', '07:05', '24:00');
});

test('time slot labels use twelve hour format with am pm', function () {
    $options = TimeSlots::options();

    expect($options['00:00'])->toBe('12:00 AM')
        ->and($options['07:00'])->toBe('07:00 AM')
        ->and($options['12:00'])->toBe('12:00 PM')
        ->and($options['13:45'])->toBe('01:45 PM')
        ->and($options['23:45'])->toBe('11:45 PM');
});
