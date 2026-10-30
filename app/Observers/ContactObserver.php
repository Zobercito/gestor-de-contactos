<?php

namespace App\Observers;

use App\Models\Contact;
use App\Services\ActivityLogger;

class ContactObserver
{
    public function __construct(private ActivityLogger $logger) {}

    public function created(Contact $contact): void
    {
        $this->logger->log('created', $contact, "Creó el contacto {$contact->name}", [
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);
    }

    public function updated(Contact $contact): void
    {
        $changes = collect($contact->getChanges())
            ->except(['updated_at', 'updated_by'])
            ->all();

        if ($changes === []) {
            return;
        }

        $this->logger->log('updated', $contact, "Actualizó el contacto {$contact->name}", [
            'changes' => $changes,
        ]);
    }

    public function deleted(Contact $contact): void
    {
        $this->logger->log('deleted', $contact, "Eliminó el contacto {$contact->name}", [
            'name' => $contact->name,
            'email' => $contact->email,
        ]);
    }
}
