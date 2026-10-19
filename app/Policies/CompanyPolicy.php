<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Company $company): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Company $company): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin()
            || $user->isSalesManager()
            || $company->assigned_user_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
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
