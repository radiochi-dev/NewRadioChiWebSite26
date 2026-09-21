<?php

namespace App\Policies;

use App\Models\NewsletterSubscriber;
use App\Models\User;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->canViewBackofficeContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->canManageBackofficeContent();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageBackofficeContent();
    }
}
