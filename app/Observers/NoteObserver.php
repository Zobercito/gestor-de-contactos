<?php

namespace App\Observers;

use App\Models\Note;
use App\Services\ActivityLogger;

class NoteObserver
{
    public function __construct(private ActivityLogger $logger) {}

    public function created(Note $note): void
    {
        $note->loadMissing('contact');

        if (! $note->contact) {
            return;
        }

        $this->logger->log('note_added', $note->contact, "Añadió una nota a {$note->contact->name}", [
            'note_id' => $note->id,
            'excerpt' => str($note->body)->limit(120)->toString(),
        ]);
    }

    public function deleted(Note $note): void
    {
        $note->loadMissing('contact');

        if (! $note->contact) {
            return;
        }

        $this->logger->log('updated', $note->contact, "Eliminó una nota de {$note->contact->name}", [
            'note_id' => $note->id,
        ]);
    }
}
