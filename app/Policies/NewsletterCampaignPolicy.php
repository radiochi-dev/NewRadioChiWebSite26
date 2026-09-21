<?php

namespace App\Policies;

use App\Models\NewsletterCampaign;
use App\Models\User;

class NewsletterCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, NewsletterCampaign $newsletterCampaign): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, NewsletterCampaign $newsletterCampaign): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, NewsletterCampaign $newsletterCampaign): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
