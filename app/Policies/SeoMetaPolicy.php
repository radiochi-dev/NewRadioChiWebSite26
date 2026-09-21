<?php

namespace App\Policies;

use App\Models\SeoMeta;
use App\Models\User;

class SeoMetaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, SeoMeta $seoMeta): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, SeoMeta $seoMeta): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, SeoMeta $seoMeta): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
