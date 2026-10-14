@extends('layouts.app')

@section('title', 'Deals Pipeline')
@section('page_title', 'Opportunities Pipeline')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Deals &amp; Opportunities</h3>
        <p class="text-muted small mb-0">Manage customer deals, stage probabilities, and projected revenue.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('deals.kanban') }}" class="btn btn-sm btn-outline-secondary shadow-sm fw-semibold">
            <i class="bi bi-kanban me-1"></i> Switch to Kanban
        </a>
        <a href="{{ route('deals.export', request()->query()) }}" class="btn btn-sm btn-outline-success shadow-sm fw-semibold">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('deals.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Add Deal
        </a>
    </div>
</div>

<!-- Financial Summary Bar -->
<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <div class="row g-3 text-center">
            <div class="col-sm-4 border-end">
                <span class="text-muted small fw-semibold text-uppercase">Total Active Pipeline</span>
                <h4 class="fw-bold text-primary mb-0 mt-1">${{ number_format($totalOpenAmount, 2) }}</h4>
            </div>
            <div class="col-sm-4 border-end">
                <span class="text-muted small fw-semibold text-uppercase">Closed Won Total</span>
                <h4 class="fw-bold text-success mb-0 mt-1">${{ number_format($totalWonAmount, 2) }}</h4>
            </div>
            <div class="col-sm-4">
                <span class="text-muted small fw-semibold text-uppercase">Deals Listed</span>
                <h4 class="fw-bold text-dark mb-0 mt-1">{{ $deals->total() }} Deals</h4>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('deals.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Deal or Company</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Title, client..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Pipeline Stage</label>
                <select name="stage" class="form-select form-select-sm">
                    <option value="">All Stages</option>
                    @foreach(\App\Models\Deal::STAGES as $key => $meta)
                        <option value="{{ $key }}" {{ ($filters['stage'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $meta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Assigned Representative</label>
                <select name="assigned_user_id" class="form-select form-select-sm">
                    <option value="">All Sales Reps</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ ($filters['assigned_user_id'] ?? '') == $usr->id ? 'selected' : '' }}>
                            {{ $usr->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('deals.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Deals Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Opportunity Title</th>
                    <th>Company / Account</th>
                    <th>Stage</th>
                    <th>Amount ($)</th>
                    <th>Probability</th>
                    <th>Expected Close</th>
                    <th>Assigned Rep</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deals as $deal)
                    <tr>
                        <td>
                            <a href="{{ route('deals.show', $deal) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $deal->title }}
                            </a>
                            <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $deal->deal_type) }}</div>
                        </td>
                        <td>
                            @if($deal->company)
                                <a href="{{ route('companies.show', $deal->company) }}" class="text-decoration-none text-muted">
                                    <i class="bi bi-building me-1"></i> {{ $deal->company->name }}
                                </a>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $deal->stage_badge_color }}-subtle text-{{ $deal->stage_badge_color }} border border-{{ $deal->stage_badge_color }}-subtle">
                                {{ $deal->stage_label }}
                            </span>
                        </td>
                        <td class="fw-bold text-dark">${{ number_format($deal->amount, 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $deal->stage_badge_color }}" style="width: {{ $deal->probability }}%"></div>
                                </div>
                                <span class="small text-muted">{{ $deal->probability }}%</span>
                            </div>
                        </td>
                        <td class="small text-muted">
                            {{ $deal->expected_close_date ? $deal->expected_close_date->format('M d, Y') : '&mdash;' }}
                        </td>
                        <td>
                            <span class="small fw-semibold text-dark">{{ $deal->assignedUser?->name ?? 'Unassigned' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('deals.show', $deal) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('deals.edit', $deal) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('deals.destroy', $deal) }}" class="d-inline" onsubmit="return confirm('Delete deal \'{{ $deal->title }}\'?');">
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
                            <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                            No deals found matching the criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($deals->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $deals->links() }}
        </div>
    @endif
</div>
@endsection
