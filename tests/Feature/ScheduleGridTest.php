<?php

use App\Enums\DayOfWeek;
use App\Filament\Pages\ScheduleGrid;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\Subject;
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
    Schedule::factory()->for($subject)->block(DayOfWeek::Monday->value, '07:00', '08:30')->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSet('periodId', $current->id)
        ->assertSee('Programación')
        ->assertSee('Lunes')
        ->assertSee('07:30');
});

test('the grid excludes blocks of other users', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $foreignSubject = Subject::factory()->create(['name' => 'Materia Ajena']);
    Schedule::factory()->for($foreignSubject)->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)->assertDontSee('Materia Ajena');
});

test('the grid filters blocks by the selected period', function () {
    $admin = User::factory()->admin()->create();
    $current = Period::factory()->for($admin)->current()->create();
    $other = Period::factory()->for($admin)->create();
    $currentSubject = Subject::factory()->for($current)->create(['name' => 'Materia Actual']);
    $otherSubject = Subject::factory()->for($other)->create(['name' => 'Materia Anterior']);
    Schedule::factory()->for($currentSubject)->create();
    Schedule::factory()->for($otherSubject)->create();

    $this->actingAs($admin);

    Livewire::test(ScheduleGrid::class)
        ->assertSee('Materia Actual')
        ->assertDontSee('Materia Anterior')
        ->set('periodId', $other->id)
        ->assertSee('Materia Anterior')
        ->assertDontSee('Materia Actual');
});
