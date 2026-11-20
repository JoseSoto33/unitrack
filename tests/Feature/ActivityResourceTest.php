<?php

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use App\Filament\Resources\Activities\Pages\CreateActivity;
use App\Filament\Resources\Activities\Pages\EditActivity;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Models\Activity;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('admins can list only their own activities', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $own = Activity::factory()->for($subject)->count(2)->create();
    $foreign = Activity::factory()->count(2)->create();

    $this->actingAs($admin);

    Livewire::test(ListActivities::class)
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords($foreign);
});

test('students cannot access activities in the panel', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin/activities')->assertForbidden();
});

test('admins can create an activity', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();

    $this->actingAs($admin);

    Livewire::test(CreateActivity::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'title' => 'Examen parcial',
            'type' => ActivityType::Exam->value,
            'modality' => ActivityModality::Individual->value,
            'assigned_on' => '2026-02-01',
            'due_at' => '2026-02-15 23:59:00',
            'delivered_at' => null,
            'percentage' => 20,
            'grade' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Activity::class, [
        'title' => 'Examen parcial',
        'subject_id' => $subject->id,
        'user_id' => $admin->id,
        'net_value' => 4,
    ]);
});

test('activity percentages cannot exceed 100 per subject', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    Activity::factory()->for($subject)->create(['percentage' => 80]);

    $this->actingAs($admin);

    Livewire::test(CreateActivity::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'title' => 'Quiz 2',
            'type' => ActivityType::Quiz->value,
            'modality' => ActivityModality::Individual->value,
            'assigned_on' => '2026-02-01',
            'due_at' => '2026-02-15 23:59:00',
            'percentage' => 30,
        ])
        ->call('create')
        ->assertHasFormErrors(['percentage']);
});

test('editing an activity does not count its own percentage twice', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create(['percentage' => 80]);

    $this->actingAs($admin);

    Livewire::test(EditActivity::class, ['record' => $activity->id])
        ->fillForm(['percentage' => 90])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Activity::class, [
        'id' => $activity->id,
        'percentage' => 90,
    ]);
});

test('activity grade cannot exceed the subject scale', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create(['max_grade' => 20]);

    $this->actingAs($admin);

    Livewire::test(CreateActivity::class)
        ->fillForm([
            'subject_id' => $subject->id,
            'title' => 'Examen parcial',
            'type' => ActivityType::Exam->value,
            'modality' => ActivityModality::Individual->value,
            'assigned_on' => '2026-02-01',
            'due_at' => '2026-02-15 23:59:00',
            'percentage' => 20,
            'grade' => 25,
        ])
        ->call('create')
        ->assertHasFormErrors(['grade' => 'max']);
});

test('activities cannot be attached to a subject of another user', function () {
    $admin = User::factory()->admin()->create();
    $foreignSubject = Subject::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CreateActivity::class)
        ->fillForm([
            'subject_id' => $foreignSubject->id,
            'title' => 'Examen parcial',
            'type' => ActivityType::Exam->value,
            'modality' => ActivityModality::Individual->value,
            'assigned_on' => '2026-02-01',
            'due_at' => '2026-02-15 23:59:00',
            'percentage' => 20,
        ])
        ->call('create')
        ->assertHasFormErrors(['subject_id' => 'in']);
});

test('admins can edit their own activities', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create();

    $this->actingAs($admin);

    Livewire::test(EditActivity::class, ['record' => $activity->id])
        ->fillForm(['title' => 'Examen final'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Activity::class, [
        'id' => $activity->id,
        'title' => 'Examen final',
    ]);
});

test('admins cannot edit activities of other users', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditActivity::class, ['record' => $activity->id])->assertForbidden();
});

test('admins can delete their own activities', function () {
    $admin = User::factory()->admin()->create();
    $period = Period::factory()->for($admin)->create();
    $subject = Subject::factory()->for($period)->create();
    $activity = Activity::factory()->for($subject)->create();

    $this->actingAs($admin);

    Livewire::test(EditActivity::class, ['record' => $activity->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(Activity::class, ['id' => $activity->id]);
});
