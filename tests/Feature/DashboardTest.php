<?php

use App\Models\Contact;
use App\Models\Segment;
use App\Models\User;

it('shows totals and birthdays to authenticated users', function () {
    $user = User::factory()->editor()->create();
    Segment::factory()->count(2)->create();
    Contact::factory()->count(3)->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Contactos')
        ->assertSee('Cumpleaños');
});

it('allows every role to see the dashboard', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get('/dashboard')
        ->assertOk();
})->with(['admin', 'editor', 'viewer']);
