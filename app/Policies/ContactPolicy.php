<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Contact $contact): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSalesManager()
            || $contact->assigned_user_id === $user->id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin() || $user->isSalesManager();
    }

    public function export(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin() || $user->isSalesManager();
    }
}
