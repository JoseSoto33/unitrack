<?php

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;
use App\Filament\Resources\Subjects\Pages\CreateSubject;
use App\Filament\Resources\Subjects\Pages\EditSubject;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admins can list only their own subjects', function () {
    $admin = User::factory()->admin()->create();
    $ownPeriod = Period::factory()->for($admin)->create();
    $own = Subject::factory()->for($ownPeriod)->count(2)->create();
    $foreign = Subject::factory()->count(2)->create();

    $this->actingAs($admin);

    Livewire::test(ListSubjects::class)
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords($foreign);
});

test('students cannot access subjects in the panel', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/subjects')->assertForbidden();
});

test('admins can create a subject', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSubject::class)
        ->fillForm([
            'period_id' => $period->id,
            'name' => 'Programación',
            'professor' => 'Ana Pérez',
            'credits' => 4,
            'modality' => SubjectModality::InPerson->value,
            'max_grade' => 20,
            'min_passing_grade' => 10,
            'final_grade' => null,
            'status' => SubjectStatus::Pending->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Subject::class, [
        'name' => 'Programación',
        'period_id' => $period->id,
    ]);
});

test('subject grades cannot exceed the max grade', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();

    $this->actingAs($admin);

    Livewire::test(CreateSubject::class)
        ->fillForm([
            'period_id' => $period->id,
            'name' => 'Programación',
            'credits' => 4,
            'modality' => SubjectModality::InPerson->value,
            'max_grade' => 20,
            'min_passing_grade' => 25,
            'final_grade' => 30,
            'status' => SubjectStatus::Pending->value,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'min_passing_grade' => 'lte',
            'final_grade' => 'lte',
        ]);
});

test('subjects cannot be attached to a period of another user', function () {
    $admin = User::factory()->admin()->create();
    $foreignPeriod = Period::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreateSubject::class)
        ->fillForm([
            'period_id' => $foreignPeriod->id,
            'name' => 'Programación',
            'credits' => 4,
            'modality' => SubjectModality::InPerson->value,
            'max_grade' => 20,
            'min_passing_grade' => 10,
            'status' => SubjectStatus::Pending->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['period_id' => 'in']);
});

test('admins can edit their own subjects', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(EditSubject::class, ['record' => $subject->id])
        ->fillForm(['name' => 'Programación Avanzada'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Subject::class, [
        'id' => $subject->id,
        'name' => 'Programación Avanzada',
    ]);
});

test('admins cannot edit subjects of other users', function () {
    $admin = User::factory()->admin()->create();
    $subject = Subject::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditSubject::class, ['record' => $subject->id])->assertForbidden();
});

test('admins can delete their own subjects', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(EditSubject::class, ['record' => $subject->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Subject::class, ['id' => $subject->id]);
});
