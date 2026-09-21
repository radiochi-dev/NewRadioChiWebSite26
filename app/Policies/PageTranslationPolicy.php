<?php

namespace App\Policies;

use App\Models\PageTranslation;
use App\Models\User;

class PageTranslationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, PageTranslation $pageTranslation): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, PageTranslation $pageTranslation): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, PageTranslation $pageTranslation): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
