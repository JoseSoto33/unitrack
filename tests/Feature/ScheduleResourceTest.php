<?php

use App\Enums\DayOfWeek;
use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Models\ClassDay;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TimeRange;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admins can list only their own schedules', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $own = Schedule::factory()->for($subject)->count(2)->create();
    $foreign = Schedule::factory()->count(2)->create();

    $this->actingAs($admin);

    Livewire::test(ListSchedules::class)
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords($foreign);
});

test('students cannot access schedules in the panel', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/schedules')->assertForbidden();
});

test('admins can create a schedule with multiple days and ranges', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                        ['starts_at' => '09:00', 'ends_at' => '10:00'],
                    ],
                ],
                [
                    'day_of_week' => DayOfWeek::Wednesday->value,
                    'timeRanges' => [
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $schedule = Schedule::query()->where('subject_id', $subject->id)->first();

    expect($schedule)->not->toBeNull()
        ->and($schedule->user_id)->toBe($admin->id)
        ->and($schedule->classDays()->count())->toBe(2)
        ->and(TimeRange::query()->whereHas('classDay', fn ($query) => $query->where('schedule_id', $schedule->id))->count())->toBe(3);
});

test('ranges must last at least thirty minutes', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '07:15']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('identical ranges on the same day are rejected', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('overlapping ranges on the same day are rejected', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                        ['starts_at' => '07:30', 'ends_at' => '08:30'],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('adjacent ranges on the same day are allowed', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [
                        ['starts_at' => '07:00', 'ends_at' => '08:00'],
                        ['starts_at' => '08:00', 'ends_at' => '09:00'],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('the same day cannot be repeated in one schedule', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '09:00', 'ends_at' => '10:00']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('ranges cannot overlap another subject schedule of the same day and period', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $otherSubject = Subject::factory()->for($period)->create();
    $existing = Schedule::factory()->for($subject)->create();
    $existingDay = ClassDay::factory()->for($existing)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($existingDay)->create(['starts_at' => '07:00', 'ends_at' => '08:00']);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $otherSubject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '07:30', 'ends_at' => '08:30']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('schedules cannot be attached to a subject of another user', function () {
    $admin = User::factory()->admin()->create();
    $foreignSubject = Subject::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $foreignSubject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['subject_id' => 'in']);
});

test('admins can edit their own schedules', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:00']);

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])
        ->fillForm([
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Tuesday->value,
                    'timeRanges' => [['starts_at' => '09:00', 'ends_at' => '10:00']],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($schedule->classDays()->first()->day_of_week)->toBe(DayOfWeek::Tuesday)
        ->and($schedule->classDays()->first()->timeRanges()->first()->starts_at)->toBe('09:00:00');
});

test('admins cannot edit schedules of other users', function () {
    $admin = User::factory()->admin()->create();
    $schedule = Schedule::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])->assertForbidden();
});

test('admins can delete their own schedules with their days and ranges', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create();
    $range = TimeRange::factory()->for($day)->create();

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Schedule::class, ['id' => $schedule->id]);
    $this->assertDatabaseMissing(ClassDay::class, ['id' => $day->id]);
    $this->assertDatabaseMissing(TimeRange::class, ['id' => $range->id]);
});
