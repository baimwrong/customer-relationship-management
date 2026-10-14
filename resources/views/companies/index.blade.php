@extends('layouts.app')

@section('title', 'Companies & Accounts')
@section('page_title', 'Companies Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Companies &amp; Accounts</h3>
        <p class="text-muted small mb-0">Manage enterprise organizations, partner firms, and institutional clients.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('companies.export', request()->query()) }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Add Company
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('companies.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Company</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, city, email..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Industry</label>
                <select name="industry" class="form-select form-select-sm">
                    <option value="">All Industries</option>
                    @foreach($industries as $ind)
                        <option value="{{ $ind }}" {{ ($filters['industry'] ?? '') === $ind ? 'selected' : '' }}>
                            {{ $ind }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="customer" {{ ($filters['status'] ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="prospect" {{ ($filters['status'] ?? '') === 'prospect' ? 'selected' : '' }}>Prospect</option>
                    <option value="partner" {{ ($filters['status'] ?? '') === 'partner' ? 'selected' : '' }}>Partner</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Companies Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Company Name</th>
                    <th>Industry</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="text-center">Contacts</th>
                    <th class="text-center">Deals</th>
                    <th>Annual Revenue</th>
                    <th>Assigned Rep</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>
                            <a href="{{ route('companies.show', $company) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $company->name }}
                            </a>
                            <div class="small text-muted">{{ $company->website ?? $company->phone ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $company->industry ?? 'Other' }}</span>
                        </td>
                        <td class="small text-muted">
                            {{ $company->city ? $company->city . ', ' . $company->country : '—' }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $company->status === 'customer' ? 'success' : ($company->status === 'active' ? 'primary' : 'secondary') }}-subtle text-{{ $company->status === 'customer' ? 'success' : ($company->status === 'active' ? 'primary' : 'secondary') }} text-capitalize">
                                {{ $company->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border">{{ $company->contacts_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border">{{ $company->deals_count }}</span>
                        </td>
                        <td class="fw-semibold text-dark">
                            {{ $company->annual_revenue ? '$' . number_format($company->annual_revenue, 2) : '—' }}
                        </td>
                        <td class="small fw-semibold text-dark">{{ $company->assignedUser?->name ?? 'Unassigned' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-secondary" title="View 360 Account">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('companies.destroy', $company) }}" class="d-inline" onsubmit="return confirm('Delete company \'{{ $company->name }}\'? All related references will be updated.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-building fs-2 d-block text-secondary mb-2"></i>
                            No companies found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($companies->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $companies->links() }}
        </div>
    @endif
</div>
@endsection
