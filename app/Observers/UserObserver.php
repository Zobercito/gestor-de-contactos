<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function __construct(private ActivityLogger $logger) {}

    public function created(User $user): void
    {
        $this->logger->log('created', $user, "Creó el usuario {$user->name}", [
            'email' => $user->email,
            'role' => $user->role?->value,
        ]);
    }

    public function updated(User $user): void
    {
        $changes = collect($user->getChanges())
            ->except(['updated_at', 'remember_token', 'password'])
            ->all();

        if ($changes === []) {
            return;
        }

        $this->logger->log('updated', $user, "Actualizó el usuario {$user->name}", [
            'changes' => $changes,
        ]);
    }

    public function deleted(User $user): void
    {
        $this->logger->log('deleted', $user, "Eliminó el usuario {$user->name}");
    }
}
