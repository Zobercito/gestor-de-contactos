<?php

use App\Livewire\Contacts\Index;
use App\Livewire\Contacts\Show;
use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Note;
use App\Models\Segment;
use App\Models\User;
use Livewire\Livewire;

it('searches contacts by name, email or company', function () {
    $this->actingAs(User::factory()->viewer()->create());
    Contact::factory()->create(['name' => 'María Fernanda', 'email' => 'maria@example.com']);
    Contact::factory()->create(['name' => 'Carlos Ruiz', 'email' => 'carlos@example.com']);

    Livewire::test(Index::class)
        ->set('search', 'maría')
        ->assertSee('María Fernanda')
        ->assertDontSee('Carlos Ruiz');
});

it('filters contacts by segment', function () {
    $segment = Segment::factory()->create();
    $inside = Contact::factory()->create(['name' => 'Dentro del segmento']);
    $outside = Contact::factory()->create(['name' => 'Fuera del segmento']);
    $inside->segments()->attach($segment);

    $this->actingAs(User::factory()->viewer()->create());

    Livewire::test(Index::class)
        ->set('filters.segment', (string) $segment->id)
        ->assertSee('Dentro del segmento')
        ->assertDontSee('Fuera del segmento');
});

it('adds a note and records activity', function () {
    $contact = Contact::factory()->create();
    $editor = User::factory()->editor()->create();
    $this->actingAs($editor);

    Livewire::test(Show::class, ['contact' => $contact])
        ->set('note', 'Prefiere llamadas por la tarde.')
        ->call('addNote')
        ->assertHasNoErrors();

    expect($contact->notes()->count())->toBe(1)
        ->and(ActivityLog::where('event', 'note_added')->where('subject_id', $contact->id)->count())->toBe(1);
});

it('forbids viewers from adding notes', function () {
    $contact = Contact::factory()->create();
    $this->actingAs(User::factory()->viewer()->create());

    Livewire::test(Show::class, ['contact' => $contact])
        ->set('note', 'Intento sin permiso')
        ->call('addNote')
        ->assertForbidden();

    expect($contact->notes()->count())->toBe(0);
});

it('lets an author delete their own note', function () {
    $editor = User::factory()->editor()->create();
    $contact = Contact::factory()->create();
    $note = Note::factory()->create(['contact_id' => $contact->id, 'user_id' => $editor->id]);
    $this->actingAs($editor);

    Livewire::test(Show::class, ['contact' => $contact])
        ->call('deleteNote', $note->id)
        ->assertHasNoErrors();

    expect(Note::find($note->id))->toBeNull();
});
