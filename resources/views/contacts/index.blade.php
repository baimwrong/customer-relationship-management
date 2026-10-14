@extends('layouts.app')

@section('title', 'Contacts Directory')
@section('page_title', 'Contacts Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Contacts Directory</h3>
        <p class="text-muted small mb-0">Customer decision makers, procurement contacts, and stakeholders.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('contacts.export', request()->query()) }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('contacts.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Add Contact
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('contacts.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Contacts</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, email, job title..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Filter by Company</label>
                <select name="company_id" class="form-select form-select-sm">
                    <option value="">All Companies</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ ($filters['company_id'] ?? '') == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Contact Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="vip" {{ ($filters['status'] ?? '') === 'vip' ? 'selected' : '' }}>VIP / Executive</option>
                    <option value="lead" {{ ($filters['status'] ?? '') === 'lead' ? 'selected' : '' }}>Lead Contact</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Contact Person</th>
                    <th>Job Title &amp; Department</th>
                    <th>Associated Company</th>
                    <th>Direct Contact</th>
                    <th>Status</th>
                    <th>Assigned Rep</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-info-subtle text-info fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    {{ substr($contact->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('contacts.show', $contact) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $contact->full_name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $contact->job_title ?? '—' }}</div>
                            <small class="text-muted">{{ $contact->department ?? 'General' }}</small>
                        </td>
                        <td>
                            @if($contact->company)
                                <a href="{{ route('companies.show', $contact->company) }}" class="text-decoration-none text-muted">
                                    <i class="bi bi-building me-1"></i> {{ $contact->company->name }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-decoration-none small d-block text-dark">
                                    <i class="bi bi-envelope me-1 text-muted"></i> {{ $contact->email }}
                                </a>
                            @endif
                            @if($contact->phone)
                                <span class="small text-muted d-block"><i class="bi bi-telephone me-1"></i> {{ $contact->phone }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $contact->status === 'vip' ? 'warning' : ($contact->status === 'active' ? 'success' : 'secondary') }}-subtle text-{{ $contact->status === 'vip' ? 'warning' : ($contact->status === 'active' ? 'success' : 'secondary') }} text-uppercase" style="font-size: 0.7rem;">
                                {{ $contact->status }}
                            </span>
                        </td>
                        <td class="small fw-semibold text-dark">{{ $contact->assignedUser?->name ?? 'Unassigned' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="d-inline" onsubmit="return confirm('Delete contact \'{{ $contact->full_name }}\'?');">
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
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-2 d-block text-secondary mb-2"></i>
                            No contacts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contacts->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $contacts->links() }}
        </div>
    @endif
</div>
@endsection
