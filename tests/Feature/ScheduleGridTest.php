<?php

use App\Enums\DayOfWeek;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Livewire\ScheduleGrid;
use App\Models\ClassDay;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TimeRange;
use App\Models\User;
use Livewire\Livewire;

test('admins see the grid tab by default on the schedules page', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($current)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:30']);

    $this->actingAs($admin);

    $this->get('/admin/schedules')
        ->assertOk()
        ->assertSee('Cuadro de horario')
        ->assertSee('Bloques agregados')
        ->assertSee('Programación');
});

test('students cannot view the schedules page', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/schedules')->assertForbidden();
});

test('the create action is visible on both tabs', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(ListSchedules::class)
        ->assertSee('Crear')
        ->set('tab', 'list')
        ->assertSee('Crear');
});

test('the list tab shows the blocks table', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $own = Schedule::factory()->for($subject)->count(2)->create();
    $foreign = Schedule::factory()->count(2)->create();

    $this->actingAs($admin);

    Livewire::test(ListSchedules::class)
        ->set('tab', 'list')
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords($foreign);
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

test('the grid spans from the earliest start to the latest end plus one quarter hour', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($current)->create();
    $schedule = Schedule::factory()->for($subject)->create();
    $monday = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    $tuesday = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Tuesday]);
    TimeRange::factory()->for($monday)->create(['starts_at' => '07:15', 'ends_at' => '08:45']);
    TimeRange::factory()->for($tuesday)->create(['starts_at' => '16:00', 'ends_at' => '17:00']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSee('07:15')
        ->assertSee('08:30')
        ->assertSee('16:45')
        ->assertSee('17:00')
        ->assertDontSee('07:00')
        ->assertDontSee('06:45');
});

test('the grid rounds the derived range outwards to quarter hours', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($current)->create();
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:10', 'ends_at' => '08:20']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSee('07:00')
        ->assertSee('08:15')
        ->assertSee('08:30')
        ->assertDontSee('08:45');
});

test('the grid shows an empty state when the period has no blocks', function () {
    $admin = User::factory()->admin()->create();
    Period::factory()->for($admin)->current()->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSee('No hay bloques de horario en este período');
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
