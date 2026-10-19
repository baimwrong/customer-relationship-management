<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Lead $lead): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSalesManager()
            || $lead->assigned_user_id === $user->id;
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
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
