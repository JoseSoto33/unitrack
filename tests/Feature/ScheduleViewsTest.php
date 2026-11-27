<?php

use App\Enums\DayOfWeek;
use App\Filament\Pages\ScheduleGrid;
use App\Models\ClassDay;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TimeRange;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function mondayInsidePeriod(): string
{
    return Carbon::parse('next monday')->format('Y-m-d');
}

test('the schedule page defaults to the week view', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)->assertSet('activeView', 'week');
});

test('the day view shows blocks of the matching weekday inside the period', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($period)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:00']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->call('setView', 'day')
        ->set('dayDate', mondayInsidePeriod())
        ->assertSee('Programación')
        ->assertSee('Lunes');
});

test('the day view hides blocks when the date is outside the period', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($period)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->call('setView', 'day')
        ->set('dayDate', now()->addMonths(8)->format('Y-m-d'))
        ->assertSee('La fecha seleccionada está fuera del rango del período elegido')
        ->assertDontSee('Programación');
});

test('the month view lists weekly occurrences inside the period', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($period)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create(['starts_at' => '07:00', 'ends_at' => '08:00']);

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->call('setView', 'month')
        ->assertSee(Carbon::now()->locale('es')->translatedFormat('F Y'))
        ->assertSee('Programación')
        ->assertSee('07:00');
});

test('the month view navigates between months', function () {
    $admin = User::factory()->admin()->create();
    Period::factory()->for($admin)->current()->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->call('setView', 'month')
        ->call('nextMonth')
        ->assertSee(Carbon::now()->addMonthNoOverflow()->locale('es')->translatedFormat('F Y'))
        ->call('previousMonth')
        ->assertSee(Carbon::now()->locale('es')->translatedFormat('F Y'));
});

test('the month view hides blocks outside the period', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($period)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create();

    $this->actingAs($admin);

    $component = Livewire::test(ScheduleGrid::class)->call('setView', 'month');

    foreach (range(1, 8) as $i) {
        $component->call('nextMonth');
    }

    $component->assertDontSee('Programación');
});

test('clicking a month day opens the day view for that date', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->current()->create();
    $subject = Subject::factory()->for($period)->create(['name' => 'Programación']);
    $schedule = Schedule::factory()->for($subject)->create();
    $day = ClassDay::factory()->for($schedule)->create(['day_of_week' => DayOfWeek::Monday]);
    TimeRange::factory()->for($day)->create();

    $this->actingAs($admin);

    $monday = mondayInsidePeriod();

    Livewire::test(ScheduleGrid::class)
        ->call('setView', 'month')
        ->call('goToDay', $monday)
        ->assertSet('activeView', 'day')
        ->assertSet('dayDate', $monday)
        ->assertSee('Programación');
});
