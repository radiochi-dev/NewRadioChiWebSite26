<?php

namespace App\Policies;

use App\Models\User;

class BackofficeContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, mixed $record): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, mixed $record): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, mixed $record): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
