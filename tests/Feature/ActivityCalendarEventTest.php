<?php

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Subject;
use Filament\Support\Colors\Color;

test('activities map to calendar events with type color and props', function () {
    $subject = Subject::factory()->create(['name' => 'Programación']);
    $activity = Activity::factory()->for($subject)->create([
        'title' => 'Examen parcial',
        'type' => ActivityType::Exam,
        'due_at' => now()->addWeek()->format('Y-m-d 23:59:00'),
    ]);

    $event = $activity->toCalendarEvent()->toArray();

    expect($event['id'])->toBe($activity->id)
        ->and($event['title'])->toBe('Examen parcial — Programación')
        ->and($event['color'])->toBe(Color::hex(Activity::TYPE_COLORS['exam']))
        ->and($event['extendedProps']['type'])->toBe('Examen')
        ->and($event['extendedProps']['subject'])->toBe('Programación')
        ->and($event['extendedProps']['status'])->toBe('Pendiente');
});
