@extends('layouts.app')

@section('title', $company->name)
@section('page_title', 'Account Overview')

@section('content')
<!-- Account Header Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $company->status === 'customer' ? 'success' : 'primary' }}-subtle text-{{ $company->status === 'customer' ? 'success' : 'primary' }} border border-{{ $company->status === 'customer' ? 'success' : 'primary' }}-subtle fs-6 text-capitalize">
                        {{ $company->status }}
                    </span>
                    <span class="badge bg-light text-dark border">{{ $company->industry ?? 'General Industry' }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $company->name }}</h3>
                <div class="text-muted small">
                    @if($company->website)
                        <i class="bi bi-globe me-1"></i> <a href="{{ $company->website }}" target="_blank" class="text-decoration-none">{{ $company->website }}</a> &bull;
                    @endif
                    @if($company->city)
                        <i class="bi bi-geo-alt me-1"></i> {{ $company->city }}, {{ $company->country }} &bull;
                    @endif
                    <i class="bi bi-person-badge me-1"></i> Account Rep: <strong>{{ $company->assignedUser?->name ?? 'Unassigned' }}</strong>
                </div>
            </div>

            <div class="text-end">
                <div class="small text-muted">Annual Revenue</div>
                <h3 class="fw-bold text-dark mb-1">{{ $company->annual_revenue ? '$' . number_format($company->annual_revenue, 2) : '—' }}</h3>
                <div class="small text-muted mb-2">
                    Employees: <span class="fw-bold text-dark">{{ $company->employees_count ? number_format($company->employees_count) : 'N/A' }}</span>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('deals.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> New Deal
                    </a>
                    <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-person-plus me-1"></i> Add Contact
                    </a>
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Contacts & Opportunities -->
    <div class="col-lg-6">
        <!-- Linked Contacts -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-2 text-primary"></i> Key Contacts ({{ $company->contacts->count() }})</h6>
                <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-link text-decoration-none p-0">+ Add</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($company->contacts as $ct)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <a href="{{ route('contacts.show', $ct) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $ct->full_name }}
                            </a>
                            <div class="small text-muted">{{ $ct->job_title ?? 'Contact' }} &bull; {{ $ct->department ?? 'General' }}</div>
                            <div class="small text-muted">
                                @if($ct->email) <a href="mailto:{{ $ct->email }}" class="text-decoration-none me-2"><i class="bi bi-envelope me-1"></i>{{ $ct->email }}</a> @endif
                                @if($ct->phone) <span><i class="bi bi-telephone me-1"></i>{{ $ct->phone }}</span> @endif
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border text-capitalize">{{ $ct->status }}</span>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No contacts associated with this company yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Linked Deals -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase me-2 text-info"></i> Associated Opportunities ({{ $company->deals->count() }})</h6>
                <a href="{{ route('deals.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-link text-decoration-none p-0">+ New Deal</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($company->deals as $dl)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <a href="{{ route('deals.show', $dl) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $dl->title }}
                            </a>
                            <div class="small text-muted">
                                Stage: <span class="badge bg-{{ $dl->stage_badge_color }}-subtle text-{{ $dl->stage_badge_color }}">{{ $dl->stage_label }}</span> &bull; Prob: {{ $dl->probability }}%
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block">${{ number_format($dl->amount, 2) }}</span>
                            <small class="text-muted">{{ $dl->expected_close_date ? $dl->expected_close_date->format('M d, Y') : '' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No opportunities linked yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Account Notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text me-2 text-secondary"></i> Account Notes &amp; Profile</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-0" style="white-space: pre-line;">{{ $company->notes ?: 'No notes recorded.' }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Action Bar, Tasks & Activities Timeline -->
    <div class="col-lg-6">
        <!-- Quick Log Bar -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#logCompanyActivity">
                    <i class="bi bi-telephone me-1"></i> Log Activity
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#createCompanyTask">
                    <i class="bi bi-check2-circle me-1"></i> Schedule Task
                </button>
            </div>

            <!-- Activity Collapse Form -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="logCompanyActivity">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <input type="hidden" name="status" value="completed">

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="call">Call</option>
                                <option value="meeting">Meeting</option>
                                <option value="email">Email</option>
                                <option value="note" selected>Note</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject..." required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Activity notes..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <select name="priority" class="form-select form-select-sm w-auto">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Save Activity</button>
                    </div>
                </form>
            </div>

            <!-- Task Collapse Form -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="createCompanyTask">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <input type="hidden" name="status" value="pending">

                    <div class="row g-2 mb-2">
                        <div class="col-sm-8">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Task title..." required>
                        </div>
                        <div class="col-sm-4">
                            <input type="date" name="due_date" class="form-control form-control-sm" value="{{ now()->addDays(2)->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <select name="priority" class="form-select form-select-sm w-auto">
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-danger">Create Task</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Follow-up Tasks -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square me-2 text-danger"></i> Action Tasks</h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($company->tasks as $tsk)
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('tasks.toggle', $tsk) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width: 26px; height: 26px;">
                                    <i class="bi {{ $tsk->status === 'completed' ? 'bi-check-circle-fill text-success' : 'bi-circle' }}"></i>
                                </button>
                            </form>
                            <div>
                                <span class="{{ $tsk->status === 'completed' ? 'text-decoration-line-through text-muted' : 'fw-semibold text-dark' }}">
                                    {{ $tsk->title }}
                                </span>
                                <div class="small text-muted">Due: {{ $tsk->due_date ? $tsk->due_date->format('M d, Y') : 'No due date' }}</div>
                            </div>
                        </div>
                        <span class="badge bg-{{ $tsk->priority_meta['color'] }}-subtle text-{{ $tsk->priority_meta['color'] }}">
                            {{ $tsk->priority_meta['label'] }}
                        </span>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No tasks assigned to this account.</div>
                @endforelse
            </div>
        </div>

        <!-- Activities Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Account Interaction Log</h6>
            </div>
            <div class="card-body p-4">
                <div class="activity-timeline">
                    @forelse($company->activities as $act)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-{{ $act->type_meta['color'] }}">
                                <i class="bi {{ $act->type_meta['icon'] }}"></i>
                            </div>
                            <div class="ps-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-0">{{ $act->subject }}</h6>
                                    <span class="text-muted small">{{ $act->activity_date ? $act->activity_date->format('M d, Y H:i') : $act->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="text-muted small mb-1">{{ $act->description }}</p>
                                <span class="badge bg-light text-dark border">{{ $act->user?->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-3">No activity logs recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
