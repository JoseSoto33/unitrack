<?php

use App\Enums\DayOfWeek;
use App\Filament\Pages\ScheduleGrid;
use App\Models\ClassDay;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TimeRange;
use App\Models\User;
use Livewire\Livewire;

test('admins can view the schedule grid page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/schedule-grid')->assertOk();
});

test('students cannot view the schedule grid page', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/schedule-grid')->assertForbidden();
});

test('the grid defaults to the current period and shows own blocks', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($current)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:30']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSet('periodId', $current->id)
        ->assertSee('Programación')
        ->assertSee('Lunes')
        ->assertSee('07:30');
});

test('the grid renders every half hour row of the configured window even without blocks', function () {
    $admin = User::factory()->admin()->create();
    Period::factory()->for($admin)->current()->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->set('fromTime', '08:00')
        ->set('toTime', '10:00')
        ->assertSee('08:00')
        ->assertSee('08:30')
        ->assertSee('09:00')
        ->assertSee('09:30');
});

test('the grid uses equal width bordered columns and the required cell typography', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($current)->create();
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:00']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSeeHtml('table-fixed', false)
        ->assertSeeHtml('border border-gray-200', false)
        ->assertSeeHtml('flex flex-col', false)
        ->assertSeeHtml('text-base', false)
        ->assertSeeHtml('text-[12px]', false);
});

test('the grid excludes blocks of other users', function () {
    $admin = User::factory()->admin()->create();
    Period::factory()->for($admin)->current()->create();
    $foreignSubject = Subject::factory()->create(['name' => 'Materia Ajena']);
    $schedule = Schedule::factory()->for($foreignSubject)->create();
    $day = ClassDay::factory()->for($schedule)->create();
    TimeRange::factory()->for($day)->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)->assertDontSee('Materia Ajena');
});

test('the grid filters blocks by the selected period', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $other = Period::factory()->for($admin)->create();
    $currentSubject = Subject::factory()->for($current)->create(['name' => 'Materia Actual']);
    $otherSubject = Subject::factory()->for($other)->create(['name' => 'Materia Anterior']);
    $currentSchedule = Schedule::factory()->for($currentSubject)->create();
    $otherSchedule = Schedule::factory()->for($otherSubject)->create();
    $currentDay = ClassDay::factory()->for($currentSchedule)->create();
    $otherDay = ClassDay::factory()->for($otherSchedule)->create();
    TimeRange::factory()->for($currentDay)->create();
    TimeRange::factory()->for($otherDay)->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSee('Materia Actual')
        ->assertDontSee('Materia Anterior')
        ->set('periodId', $other->id)
        ->assertSee('Materia Anterior')
        ->assertDontSee('Materia Actual');
});
