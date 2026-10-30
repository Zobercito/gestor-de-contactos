<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->canDelete() || ($user->canWrite() && $note->user_id === $user->id);
    }
}
