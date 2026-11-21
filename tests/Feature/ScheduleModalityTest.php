<?php

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Models\ClassDay;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Livewire\Livewire;

test('hybrid subjects require a modality per day', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::Hybrid]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('hybrid subjects can have different modalities on different days', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::Hybrid]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'modality' => SubjectModality::InPerson->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
                [
                    'day_of_week' => DayOfWeek::Wednesday->value,
                    'modality' => SubjectModality::Online->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $monday = ClassDay::query()->where('day_of_week', DayOfWeek::Monday->value)->first();
    $wednesday = ClassDay::query()->where('day_of_week', DayOfWeek::Wednesday->value)->first();

    expect($monday->effective_modality)->toBe(SubjectModality::InPerson)
        ->and($wednesday->effective_modality)->toBe(SubjectModality::Online);
});

test('non hybrid days inherit the subject modality and ignore injected values', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['modality' => SubjectModality::InPerson]);

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'classDays' => [
                [
                    'day_of_week' => DayOfWeek::Monday->value,
                    'modality' => SubjectModality::Online->value,
                    'timeRanges' => [['starts_at' => '07:00', 'ends_at' => '08:00']],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $day = ClassDay::query()->first();

    expect($day->modality)->toBeNull()
        ->and($day->effective_modality)->toBe(SubjectModality::InPerson);
});
