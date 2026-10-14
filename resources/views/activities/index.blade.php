@extends('layouts.app')

@section('title', 'Activities & Interactions')
@section('page_title', 'Engagement Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Activities &amp; Interactions Log</h3>
        <p class="text-muted small mb-0">Audit trail of customer calls, executive meetings, emails, and internal memos.</p>
    </div>
    <div>
        <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#quickActivityModal">
            <i class="bi bi-plus-lg me-1"></i> Log Activity
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('activities.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Search Subject or Notes</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Subject keyword..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Activity Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach(\App\Models\Activity::TYPES as $key => $meta)
                        <option value="{{ $key }}" {{ ($filters['type'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $meta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Logged By</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Reps</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ ($filters['user_id'] ?? '') == $usr->id ? 'selected' : '' }}>
                            {{ $usr->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('activities.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Activities List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="activity-timeline">
            @forelse($activities as $act)
                <div class="timeline-item">
                    <div class="timeline-icon bg-{{ $act->type_meta['color'] }}">
                        <i class="bi {{ $act->type_meta['icon'] }}"></i>
                    </div>
                    <div class="ps-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">
                                    {{ $act->subject }}
                                    <span class="badge bg-{{ $act->type_meta['color'] }}-subtle text-{{ $act->type_meta['color'] }} ms-2" style="font-size: 0.7rem;">
                                        {{ $act->type_meta['label'] }}
                                    </span>
                                </h6>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-calendar3 me-1"></i> {{ $act->activity_date ? $act->activity_date->format('M d, Y H:i') : $act->created_at->format('M d, Y') }}
                                    &bull; Logged by: <strong class="text-dark">{{ $act->user?->name }}</strong>
                                    @if($act->duration_minutes) &bull; Duration: {{ $act->duration_minutes }} mins @endif
                                </div>
                            </div>
                            <form method="POST" action="{{ route('activities.destroy', $act) }}" onsubmit="return confirm('Delete this activity entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete Entry">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>

                        <p class="text-secondary small mb-2" style="white-space: pre-line;">{{ $act->description }}</p>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($act->company)
                                <a href="{{ route('companies.show', $act->company) }}" class="badge bg-light text-dark border text-decoration-none">
                                    <i class="bi bi-building me-1"></i> {{ $act->company->name }}
                                </a>
                            @endif
                            @if($act->contact)
                                <a href="{{ route('contacts.show', $act->contact) }}" class="badge bg-light text-dark border text-decoration-none">
                                    <i class="bi bi-person me-1"></i> {{ $act->contact->full_name }}
                                </a>
                            @endif
                            @if($act->deal)
                                <a href="{{ route('deals.show', $act->deal) }}" class="badge bg-light text-dark border text-decoration-none">
                                    <i class="bi bi-briefcase me-1"></i> {{ $act->deal->title }}
                                </a>
                            @endif
                            @if($act->lead)
                                <a href="{{ route('leads.show', $act->lead) }}" class="badge bg-light text-dark border text-decoration-none">
                                    <i class="bi bi-funnel me-1"></i> Lead: {{ $act->lead->full_name }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clock-history fs-2 d-block text-secondary mb-2"></i>
                    No interactions logged yet.
                </div>
            @endforelse
        </div>
    </div>
    @if($activities->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection
