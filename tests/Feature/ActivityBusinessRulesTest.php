<?php

use App\Models\Activity;

test('net value is computed from the subject scale and percentage', function () {
    $activity = Activity::factory()->create(['percentage' => 25]);

    expect($activity->net_value)->toBe('5.00');
});

test('net value is recomputed when the percentage changes', function () {
    $activity = Activity::factory()->create(['percentage' => 25]);

    $activity->update(['percentage' => 50]);

    expect($activity->fresh()->net_value)->toBe('10.00');
});

test('delivered activities show entregada status', function () {
    $activity = Activity::factory()->delivered()->create();

    expect($activity->delivery_status)->toBe('Entregada');
});

test('undelivered activities with a future deadline show pendiente status', function () {
    $activity = Activity::factory()->create();

    expect($activity->delivery_status)->toBe('Pendiente');
});

test('undelivered activities with a past deadline show vencida status', function () {
    $activity = Activity::factory()->overdue()->create();

    expect($activity->delivery_status)->toBe('Vencida');
});
