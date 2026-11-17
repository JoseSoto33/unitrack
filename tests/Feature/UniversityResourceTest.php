<?php

use App\Filament\Resources\Universities\Pages\CreateUniversity;
use App\Filament\Resources\Universities\Pages\EditUniversity;
use App\Filament\Resources\Universities\Pages\ListUniversities;
use App\Models\University;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

test('guests are redirected to the admin login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('students cannot access the admin panel', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get('/admin')->assertForbidden();
});

test('admins can access the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('admins can list universities', function () {
    $admin = User::factory()->admin()->create();
    $universities = University::factory()->count(3)->create();

    $this->actingAs($admin);

    Livewire::test(ListUniversities::class)
        ->assertCanSeeTableRecords($universities);
});

test('students cannot list universities', function () {
    $student = User::factory()->create();

    $this->actingAs($student);

    Livewire::test(ListUniversities::class)->assertForbidden();
});

test('admins can create a university', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CreateUniversity::class)
        ->fillForm([
            'name' => 'Universidad Central',
            'acronym' => 'UC',
            'location' => 'Caracas',
            'website' => 'https://uc.example.com',
            'email' => 'info@uc.example.com',
            'phone' => '+58 212 555 0100',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(University::class, [
        'name' => 'Universidad Central',
        'created_by' => $admin->id,
    ]);
});

test('university creation validates name website and email', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CreateUniversity::class)
        ->fillForm([
            'name' => null,
            'website' => 'not-a-url',
            'email' => 'not-an-email',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'website' => 'url',
            'email' => 'email',
        ]);
});

test('students cannot create universities', function () {
    $student = User::factory()->create();

    $this->actingAs($student);

    Livewire::test(CreateUniversity::class)->assertForbidden();
});

test('admins can edit a university', function () {
    $admin = User::factory()->admin()->create();
    $university = University::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditUniversity::class, ['record' => $university->id])
        ->fillForm(['name' => 'Universidad Actualizada'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(University::class, [
        'id' => $university->id,
        'name' => 'Universidad Actualizada',
    ]);
});

test('students cannot edit universities', function () {
    $student = User::factory()->create();
    $university = University::factory()->create();

    $this->actingAs($student);

    Livewire::test(EditUniversity::class, ['record' => $university->id])->assertForbidden();
});

test('admins can delete a university', function () {
    $admin = User::factory()->admin()->create();
    $university = University::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditUniversity::class, ['record' => $university->id])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing(University::class, ['id' => $university->id]);
});
