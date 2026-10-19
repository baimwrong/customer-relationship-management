<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount(['assignedLeads', 'assignedDeals', 'assignedCompanies'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['role' => User::ROLE_SALES_REP, 'is_active' => true]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' created successfully.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Safeguard: Ensure at least one active administrator remains
        if ($user->isAdmin() && ($validated['role'] ?? '') !== User::ROLE_ADMIN) {
            $otherActiveAdmins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->withInput()->with('error', 'Cannot change role: the system must have at least one active administrator.');
            }
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($user->isAdmin() && ! $validated['is_active']) {
            $otherActiveAdmins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->withInput()->with('error', 'Cannot deactivate the last active administrator.');
            }
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function toggleActive(User $user, Request $request): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->isAdmin() && $user->is_active) {
            $otherActiveAdmins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Cannot deactivate the last active administrator in the system.');
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User '{$user->name}' {$status}.");
    }
}
