<?php

use App\Livewire\Contacts\Form;
use App\Models\Contact;
use App\Models\Segment;
use App\Models\User;
use Livewire\Livewire;

it('lets any role browse the contact directory', function () {
    $this->actingAs(User::factory()->viewer()->create())
        ->get('/contacts')
        ->assertOk();
});

it('forbids viewers from creating contacts', function () {
    $this->actingAs(User::factory()->viewer()->create())
        ->get('/contacts/create')
        ->assertForbidden();
});

it('creates a contact with segments', function () {
    $user = User::factory()->editor()->create();
    $segments = Segment::factory()->count(2)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['contact' => null])
        ->set('name', 'Ana Torres')
        ->set('email', 'ANA@EXAMPLE.COM')
        ->set('phone', '+507 6000-1234')
        ->set('company', 'Hotel Sol')
        ->set('segmentIds', $segments->modelKeys())
        ->call('save')
        ->assertHasNoErrors();

    $contact = Contact::where('email', 'ana@example.com')->firstOrFail();

    expect($contact->name)->toBe('Ana Torres')
        ->and($contact->phone)->toBe('+50760001234')
        ->and($contact->created_by)->toBe($user->id)
        ->and($contact->segments)->toHaveCount(2);
});

it('requires a name and at least one channel', function () {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Form::class, ['contact' => null])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);

    Livewire::test(Form::class, ['contact' => null])
        ->set('name', 'Sin datos')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('rejects duplicate emails', function () {
    Contact::factory()->create(['email' => 'dup@example.com']);
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Form::class, ['contact' => null])
        ->set('name', 'Duplicado')
        ->set('email', 'dup@example.com')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

it('updates a contact', function () {
    $contact = Contact::factory()->create(['name' => 'Viejo Nombre']);
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Form::class, ['contact' => $contact])
        ->set('name', 'Nuevo Nombre')
        ->call('save')
        ->assertHasNoErrors();

    expect($contact->refresh()->name)->toBe('Nuevo Nombre');
});

it('forbids viewers from editing or deleting contacts', function () {
    $contact = Contact::factory()->create();
    $this->actingAs(User::factory()->viewer()->create());

    $this->get(route('contacts.edit', $contact))->assertForbidden();
    expect($contact->fresh())->not->toBeNull();
});

it('lets only admins delete contacts', function () {
    $contact = Contact::factory()->create();
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Form::class, ['contact' => $contact])
        ->call('delete')
        ->assertForbidden();

    expect($contact->fresh())->not->toBeNull();
});

it('deletes a contact as admin', function () {
    $contact = Contact::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Form::class, ['contact' => $contact])
        ->call('delete')
        ->assertHasNoErrors();

    expect(Contact::find($contact->id))->toBeNull();
});

it('shows the contact detail page', function () {
    $contact = Contact::factory()->create();
    $this->actingAs(User::factory()->viewer()->create())
        ->get(route('contacts.show', $contact))
        ->assertOk()
        ->assertSee($contact->name);
});
