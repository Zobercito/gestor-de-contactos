<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index;
use App\Models\User;
use Livewire\Livewire;

it('forbids non-admins from the user management page', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get('/users')
        ->assertForbidden();
})->with(['editor', 'viewer']);

it('lets admins create users', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Nueva Persona')
        ->set('email', 'nueva@example.com')
        ->set('password', 'password123')
        ->set('role', UserRole::Editor->value)
        ->call('save')
        ->assertHasNoErrors();

    $user = User::where('email', 'nueva@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Editor)
        ->and($user->email_verified_at)->not->toBeNull();
});

it('prevents changing your own role', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('updateRole', $admin->id, UserRole::Viewer->value);

    expect($admin->refresh()->role)->toBe(UserRole::Admin);
});

it('prevents deleting the last admin', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(Index::class)->call('delete', $admin->id)->assertForbidden();

    expect(User::find($admin->id))->not->toBeNull();
});

it('lets admins delete other users', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->viewer()->create();
    $this->actingAs($admin);

    Livewire::test(Index::class)->call('delete', $other->id);

    expect(User::find($other->id))->toBeNull();
});
