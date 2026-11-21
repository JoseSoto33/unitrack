<?php

use App\Enums\ActivityType;
use App\Filament\Widgets\ActivityCalendarWidget;
use App\Models\Activity;
use App\Models\User;
use Livewire\Livewire;
use Saade\FilamentFullCalendar\Data\FetchInfo;

function calendarMonthRange(): FetchInfo
{
    return FetchInfo::fromArray([
        'start' => now()->startOfMonth()->toIso8601String(),
        'end' => now()->endOfMonth()->toIso8601String(),
    ], config('app.timezone'));
}

test('admins can view the calendar page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/activity-calendar')->assertOk();
});

test('students cannot view the calendar page', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/activity-calendar')->assertForbidden();
});

test('the calendar fetches only own activities inside the visible range', function () {
    $admin = User::factory()->admin()->create();
    $own = Activity::factory()->for($admin)->create(['due_at' => now()->addDays(3)]);
    $foreign = Activity::factory()->create(['due_at' => now()->addDays(3)]);
    $outOfRange = Activity::factory()->for($admin)->create(['due_at' => now()->addDays(40)]);

    $this->actingAs($admin);

    $events = Livewire::test(ActivityCalendarWidget::class)
        ->instance()
        ->fetchEvents(calendarMonthRange());

    expect($events->pluck('id')->all())->toBe([$own->id])
        ->and($events->pluck('id')->all())->not->toContain($foreign->id, $outOfRange->id);
});

test('the calendar filters by type and delivery status', function () {
    $admin = User::factory()->admin()->create();
    $exam = Activity::factory()->for($admin)->create([
        'type' => ActivityType::Exam,
        'due_at' => now()->addDays(3),
    ]);
    $quiz = Activity::factory()->for($admin)->create([
        'type' => ActivityType::Quiz,
        'due_at' => now()->addDays(3),
    ]);
    $delivered = Activity::factory()->for($admin)->delivered()->create([
        'type' => ActivityType::Exam,
        'due_at' => now()->addDays(3),
    ]);

    $this->actingAs($admin);

    $byType = Livewire::test(ActivityCalendarWidget::class)
        ->set('filters', ['type' => ActivityType::Quiz->value])
        ->instance()
        ->fetchEvents(calendarMonthRange());

    expect($byType->pluck('id')->all())->toBe([$quiz->id]);

    $byStatus = Livewire::test(ActivityCalendarWidget::class)
        ->set('filters', ['status' => 'delivered'])
        ->instance()
        ->fetchEvents(calendarMonthRange());

    expect($byStatus->pluck('id')->all())->toBe([$delivered->id]);
});

test('the calendar is configured in spanish with month week and day views', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $config = Livewire::test(ActivityCalendarWidget::class)->instance()->config();

    expect($config['locale'])->toBe('es')
        ->and($config['initialView'])->toBe('dayGridMonth')
        ->and($config['headerToolbar']['right'])->toBe('dayGridMonth,timeGridWeek,timeGridDay')
        ->and($config['editable'])->toBeFalse();
});
