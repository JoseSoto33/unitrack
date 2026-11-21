<?php

use App\Enums\DayOfWeek;
use App\Filament\Resources\Schedules\Pages\CreateSchedule;
use App\Filament\Resources\Schedules\Pages\EditSchedule;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
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

test('admins can create a schedule block', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Schedule::class, [
        'subject_id' => $subject->id,
        'user_id' => $admin->id,
        'day_of_week' => DayOfWeek::Monday->value,
    ]);
});

test('schedule end time must be after the start time', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '08:00',
            'ends_at' => '07:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['ends_at' => 'after']);
});

test('overlapping blocks on the same day and period are rejected', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    Schedule::factory()->for($subject)->block(DayOfWeek::Monday->value, '07:00', '08:00')->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:30',
            'ends_at' => '08:30',
        ])
        ->call('create')
        ->assertHasFormErrors(['ends_at']);
});

test('adjacent blocks on the same day are allowed', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    Schedule::factory()->for($subject)->block(DayOfWeek::Monday->value, '07:00', '08:00')->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '08:00',
            'ends_at' => '09:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('blocks on a different day are allowed', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    Schedule::factory()->for($subject)->block(DayOfWeek::Monday->value, '07:00', '08:00')->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'day_of_week' => DayOfWeek::Tuesday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('blocks on the same day but a different period are allowed', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $otherPeriod = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $otherSubject = Subject::factory()->for($otherPeriod)->create();
    Schedule::factory()->for($subject)->block(DayOfWeek::Monday->value, '07:00', '08:00')->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $otherSubject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('schedules cannot be attached to a subject of another user', function () {
    $admin = User::factory()->admin()->create();
    $foreignSubject = Subject::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreateSchedule::class)
        ->fillForm([
            'subject_id' => $foreignSubject->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'starts_at' => '07:00',
            'ends_at' => '08:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['subject_id' => 'in']);
});

test('admins can edit their own schedules', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])
        ->fillForm(['starts_at' => '09:00', 'ends_at' => '10:00'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Schedule::class, [
        'id' => $schedule->id,
        'starts_at' => '09:00:00',
        'ends_at' => '10:00:00',
    ]);
});

test('admins cannot edit schedules of other users', function () {
    $admin = User::factory()->admin()->create();
    $schedule = Schedule::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])->assertForbidden();
});

test('admins can delete their own schedules', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $schedule = Schedule::factory()->for($subject)->create();

    $this->actingAs($admin);

    Livewire::test(EditSchedule::class, ['record' => $schedule->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Schedule::class, ['id' => $schedule->id]);
});
