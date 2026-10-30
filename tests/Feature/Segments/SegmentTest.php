<?php

use App\Livewire\Segments\Index;
use App\Models\Contact;
use App\Models\Segment;
use App\Models\User;
use Livewire\Livewire;

it('lets editors create and edit segments', function () {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Hoteles boutique')
        ->set('color', '#7A8B6F')
        ->set('description', 'Alianzas del sector hotelero')
        ->call('save')
        ->assertHasNoErrors();

    $segment = Segment::where('name', 'Hoteles boutique')->firstOrFail();

    Livewire::test(Index::class)
        ->call('edit', $segment->id)
        ->set('name', 'Hoteles y resorts')
        ->call('save')
        ->assertHasNoErrors();

    expect($segment->refresh()->name)->toBe('Hoteles y resorts')
        ->and($segment->color)->toBe('#7A8B6F');
});

it('validates a unique name and a hex color', function () {
    Segment::factory()->create(['name' => 'Clientes']);
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Clientes')
        ->set('color', 'rojo')
        ->call('save')
        ->assertHasErrors(['name' => 'unique', 'color']);
});

it('forbids viewers from creating segments', function () {
    $this->actingAs(User::factory()->viewer()->create());

    Livewire::test(Index::class)->call('create')->assertForbidden();
});

it('keeps contacts when deleting a segment', function () {
    $segment = Segment::factory()->create();
    $contact = Contact::factory()->create();
    $contact->segments()->attach($segment);

    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Index::class)->call('delete', $segment->id);

    expect(Segment::find($segment->id))->toBeNull()
        ->and(Contact::find($contact->id))->not->toBeNull();
});
