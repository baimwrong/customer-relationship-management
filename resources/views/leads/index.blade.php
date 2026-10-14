@extends('layouts.app')

@section('title', 'Sales Leads')
@section('page_title', 'Leads Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Sales Leads</h3>
        <p class="text-muted small mb-0">Capture, score, and qualify inbound prospects for pipeline conversion.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('leads.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Add Lead
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('leads.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Leads</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, email, company..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Lead::STATUSES as $key => $meta)
                        <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $meta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Acquisition Source</label>
                <select name="source" class="form-select form-select-sm">
                    <option value="">All Sources</option>
                    @foreach(\App\Models\Lead::SOURCES as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['source'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Lead Name</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Estimated Value</th>
                    <th>Source</th>
                    <th>Assigned Rep</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td>
                            <a href="{{ route('leads.show', $lead) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $lead->full_name }}
                            </a>
                            <div class="small text-muted">{{ $lead->email ?? $lead->phone ?? 'No contact info' }}</div>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $lead->company_name ?? '—' }}</span>
                            <div class="small text-muted">{{ $lead->job_title ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $lead->status_badge_color }}-subtle text-{{ $lead->status_badge_color }} border border-{{ $lead->status_badge_color }}-subtle">
                                {{ $lead->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $lead->score >= 70 ? 'success' : ($lead->score >= 40 ? 'warning' : 'secondary') }}" style="width: {{ $lead->score }}%"></div>
                                </div>
                                <span class="small fw-semibold text-muted">{{ $lead->score }}</span>
                            </div>
                        </td>
                        <td class="fw-bold text-dark">${{ number_format($lead->estimated_value, 2) }}</td>
                        <td>
                            <span class="badge bg-light text-muted border">{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</span>
                        </td>
                        <td class="small fw-semibold text-dark">{{ $lead->assignedUser?->name ?? 'Unassigned' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary" title="View & Convert">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="d-inline" onsubmit="return confirm('Delete lead \'{{ $lead->full_name }}\'?');">
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
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-funnel fs-2 d-block text-secondary mb-2"></i>
                            No leads found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $leads->links() }}
        </div>
    @endif
</div>
@endsection
