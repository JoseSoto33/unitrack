<?php

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Livewire\Livewire;

test('hybrid subjects require a modality per block', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::Hybrid]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['modality' => 'required']);
});

test('hybrid subjects can have different modalities on different days', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::Hybrid]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'modality' => SubjectModality::InPerson->value,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'modality' => SubjectModality::Online->value,
            'day_of_week' => DayOfWeek::Wednesday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $monday = Schedule::query()->where('day_of_week', DayOfWeek::Monday->value)->first();
    $wednesday = Schedule::query()->where('day_of_week', DayOfWeek::Wednesday->value)->first();

    expect($monday->effective_modality)->toBe(SubjectModality::InPerson)
        ->and($wednesday->effective_modality)->toBe(SubjectModality::Online);
});

test('non hybrid blocks inherit the subject modality and ignore injected values', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::InPerson]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'modality' => SubjectModality::Online->value,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $schedule = Schedule::query()->first();

    expect($schedule->modality)->toBeNull()
        ->and($schedule->effective_modality)->toBe(SubjectModality::InPerson);
});

test('a subject can be scheduled on multiple days', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    foreach ([DayOfWeek::Monday, DayOfWeek::Wednesday] as $day) {
        Livewire::test(CreateSchedule::class)
            ->fillForm([
                'subject_id' => $subject->id,
                'day_of_week' => $day->value,
                'starts_at' => '07:00',
                'ends_at' => '08:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    expect(Schedule::query()->where('subject_id', $subject->id)->count())->toBe(2);
});

test('a subject can have multiple non overlapping turns on the same day', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    foreach ([['07:00', '08:00'], ['09:00', '10:00']] as $turn) {
        Livewire::test(CreateSchedule::class)
            ->fillForm([
                'subject_id' => $subject->id,
                'day_of_week' => DayOfWeek::Monday->value,
                'starts_at' => $turn[0],
                'ends_at' => $turn[1],
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    expect(Schedule::query()->where('subject_id', $subject->id)->count())->toBe(2);
});
