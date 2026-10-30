<?php

namespace App\Livewire\Contacts;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Note;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Contact $contact;

    public string $note = '';

    public function mount(Contact $contact): void
    {
        Gate::authorize('view', $contact);
        $this->contact = $contact;
    }

    public function addNote(): void
    {
        Gate::authorize('create', Note::class);

        $validated = $this->validate([
            'note' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $this->contact->notes()->create([
            'user_id' => auth()->id(),
            'body' => $validated['note'],
        ]);

        $this->reset('note');
        $this->contact->refresh();

        Flux::toast(text: 'Nota añadida al contacto.', variant: 'success');
    }

    public function deleteNote(int $noteId): void
    {
        $note = Note::query()->where('contact_id', $this->contact->id)->findOrFail($noteId);
        Gate::authorize('delete', $note);

        $note->delete();

        Flux::toast(text: 'Nota eliminada.', variant: 'success');
    }

    public function delete(): void
    {
        Gate::authorize('delete', $this->contact);

        $this->contact->delete();
        Flux::toast(text: 'Contacto eliminado.', variant: 'success');

        $this->redirectRoute('contacts.index', navigate: true);
    }

    public function render()
    {
        $notes = $this->contact->notes()->with('user')->latest()->take(30)->get();
        $activity = ActivityLog::query()
            ->with('user')
            ->where('subject_type', $this->contact->getMorphClass())
            ->where('subject_id', $this->contact->id)
            ->latest()
            ->take(12)
            ->get();

        return view('livewire.contacts.show', [
            'notes' => $notes,
            'activity' => $activity,
        ])->layout('components.layouts.app');
    }
}
