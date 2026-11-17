<?php

use App\Filament\Resources\Careers\Pages\CreateCareer;
use App\Filament\Resources\Careers\Pages\EditCareer;
use App\Filament\Resources\Careers\Pages\ListCareers;
use App\Models\Career;
use App\Models\University;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admins can list careers', function () {
    $admin = User::factory()->admin()->create();
    $careers = Career::factory()->count(3)->create();

    $this->actingAs($admin);

    Livewire::test(ListCareers::class)
        ->assertCanSeeTableRecords($careers);
});

test('students cannot list careers', function () {
    $student = User::factory()->create();

    $this->actingAs($student);

    Livewire::test(ListCareers::class)->assertForbidden();
});

test('admins can create a career', function () {
    $admin = User::factory()->admin()->create();
    $university = University::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreateCareer::class)
        ->fillForm([
            'university_id' => $university->id,
            'title' => 'Ingeniería Informática',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Career::class, [
        'title' => 'Ingeniería Informática',
        'university_id' => $university->id,
        'created_by' => $admin->id,
    ]);
});

test('career creation requires a title and a university', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CreateCareer::class)
        ->fillForm([
            'title' => null,
            'university_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'university_id' => 'required',
        ]);
});

test('career title must be unique per university', function () {
    $admin = User::factory()->admin()->create();
    $university = University::factory()->create();
    $otherUniversity = University::factory()->create();
    Career::factory()->for($university)->create(['title' => 'Medicina']);

    $this->actingAs($admin);

    Livewire::test(CreateCareer::class)
        ->fillForm([
            'university_id' => $university->id,
            'title' => 'Medicina',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'unique']);

    Livewire::test(CreateCareer::class)
        ->fillForm([
            'university_id' => $otherUniversity->id,
            'title' => 'Medicina',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

test('students cannot create careers', function () {
    $student = User::factory()->create();

    $this->actingAs($student);

    Livewire::test(CreateCareer::class)->assertForbidden();
});

test('admins can edit a career', function () {
    $admin = User::factory()->admin()->create();
    $career = Career::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditCareer::class, ['record' => $career->id])
        ->fillForm(['title' => 'Medicina'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Career::class, [
        'id' => $career->id,
        'title' => 'Medicina',
    ]);
});

test('students cannot edit careers', function () {
    $student = User::factory()->create();
    $career = Career::factory()->create();

    $this->actingAs($student);

    Livewire::test(EditCareer::class, ['record' => $career->id])->assertForbidden();
});

test('admins can delete a career', function () {
    $admin = User::factory()->admin()->create();
    $career = Career::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditCareer::class, ['record' => $career->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Career::class, ['id' => $career->id]);
});
