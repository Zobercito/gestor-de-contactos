<?php

namespace App\Policies;

use App\Models\Segment;
use App\Models\User;

class SegmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Segment $segment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, Segment $segment): bool
    {
        return $user->canWrite();
    }

    public function delete(User $user, Segment $segment): bool
    {
        return $user->canWrite();
    }
}
