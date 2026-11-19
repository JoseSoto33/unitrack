<?php

use App\Enums\PeriodType;
use App\Filament\Resources\Periods\Pages\CreatePeriod;
use App\Filament\Resources\Periods\Pages\EditPeriod;
use App\Filament\Resources\Periods\Pages\ListPeriods;
use App\Models\Career;
use App\Models\Period;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admins can list only their own periods', function () {
    $admin = User::factory()->admin()->create();
    $own = Period::factory()->for($admin)->count(2)->create();
    $foreign = Period::factory()->count(2)->create();

    $this->actingAs($admin);

    Livewire::test(ListPeriods::class)
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords($foreign);
});

test('students cannot access periods in the panel', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/periods')->assertForbidden();
});

test('admins can create a period', function () {
    $admin = User::factory()->admin()->create();
    $career = Career::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreatePeriod::class)
        ->fillForm([
            'career_id' => $career->id,
            'type' => PeriodType::Semester->value,
            'number' => 1,
            'starts_on' => '2026-01-15',
            'ends_on' => '2026-06-30',
            'is_current' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Period::class, [
        'career_id' => $career->id,
        'user_id' => $admin->id,
        'number' => 1,
        'is_current' => true,
    ]);
});

test('period end date must be after the start date', function () {
    $admin = User::factory()->admin()->create();
    $career = Career::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreatePeriod::class)
        ->fillForm([
            'career_id' => $career->id,
            'type' => PeriodType::Semester->value,
            'number' => 1,
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-01-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['ends_on' => 'after']);
});

test('period creation requires career type number and dates', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CreatePeriod::class)
        ->fillForm([
            'career_id' => null,
            'type' => null,
            'number' => null,
            'starts_on' => null,
            'ends_on' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'career_id' => 'required',
            'type' => 'required',
            'number' => 'required',
            'starts_on' => 'required',
            'ends_on' => 'required',
        ]);
});

test('admins can edit their own periods', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();

    $this->actingAs($admin);

    Livewire::test(EditPeriod::class, ['record' => $period->id])
        ->fillForm(['number' => 2])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Period::class, [
        'id' => $period->id,
        'number' => 2,
    ]);
});

test('admins cannot edit periods of other users', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditPeriod::class, ['record' => $period->id])->assertForbidden();
});

test('admins can delete their own periods', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();

    $this->actingAs($admin);

    Livewire::test(EditPeriod::class, ['record' => $period->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Period::class, ['id' => $period->id]);
});
