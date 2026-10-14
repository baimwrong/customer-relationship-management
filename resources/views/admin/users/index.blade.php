@extends('layouts.app')

@section('title', 'Team & Users Management')
@section('page_title', 'User Administration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Sales Team &amp; User Accounts</h3>
        <p class="text-muted small mb-0">Manage roles, permissions, and active status for representatives and managers.</p>
    </div>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-person-plus me-1"></i> Add Team Member
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role &amp; Title</th>
                    <th>Department</th>
                    <th>Assigned Leads</th>
                    <th>Assigned Deals</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->isAdmin() ? 'danger' : ($user->isSalesManager() ? 'info' : 'primary') }}-subtle text-{{ $user->isAdmin() ? 'danger' : ($user->isSalesManager() ? 'info' : 'primary') }} text-uppercase mb-1" style="font-size: 0.7rem;">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                            <div class="small text-muted">{{ $user->job_title ?? 'Team Member' }}</div>
                        </td>
                        <td class="small text-muted">{{ $user->department ?? 'General' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $user->assigned_leads_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $user->assigned_deals_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}-subtle text-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== Auth::id())
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
