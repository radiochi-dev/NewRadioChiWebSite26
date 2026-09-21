<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, Page $page): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, Page $page): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
