<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Deal $deal): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSalesManager()
            || $deal->assigned_user_id === $user->id;
    }

    public function updateStage(User $user, Deal $deal): bool
    {
        return $this->update($user, $deal);
    }

    public function delete(User $user, Deal $deal): bool
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
