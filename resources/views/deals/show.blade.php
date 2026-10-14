@extends('layouts.app')

@section('title', $deal->title)
@section('page_title', 'Opportunity Overview')

@section('content')
<!-- Header Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $deal->stage_badge_color }}-subtle text-{{ $deal->stage_badge_color }} border border-{{ $deal->stage_badge_color }}-subtle fs-6">
                        {{ $deal->stage_label }}
                    </span>
                    <span class="badge bg-light text-muted border text-capitalize">{{ str_replace('_', ' ', $deal->deal_type) }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $deal->title }}</h3>
                <div class="text-muted small">
                    @if($deal->company)
                        <i class="bi bi-building me-1"></i> <a href="{{ route('companies.show', $deal->company) }}" class="text-decoration-none fw-semibold">{{ $deal->company->name }}</a> &bull;
                    @endif
                    @if($deal->contact)
                        <i class="bi bi-person me-1"></i> <a href="{{ route('contacts.show', $deal->contact) }}" class="text-decoration-none">{{ $deal->contact->full_name }}</a> &bull;
                    @endif
                    <i class="bi bi-person-badge me-1"></i> Rep: <strong>{{ $deal->assignedUser?->name ?? 'Unassigned' }}</strong>
                </div>
            </div>

            <div class="text-end">
                <div class="small text-muted">Opportunity Value</div>
                <h2 class="fw-bold text-primary mb-1">${{ number_format($deal->amount, 2) }}</h2>
                <div class="small text-muted">
                    Weighted Value: <span class="fw-bold text-dark">${{ number_format($deal->weighted_amount, 2) }}</span> ({{ $deal->probability }}%)
                </div>
                <div class="mt-2 d-flex gap-2 justify-content-end">
                    <a href="{{ route('deals.edit', $deal) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('deals.destroy', $deal) }}" onsubmit="return confirm('Delete this deal permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <hr class="my-3">

        <!-- Stage Progression Stepper -->
        <div class="mb-2">
            <span class="small fw-semibold text-muted text-uppercase d-block mb-2">Stage Progression:</span>
            <div class="deal-stage-stepper">
                @php
                    $stageKeys = array_keys(\App\Models\Deal::STAGES);
                    $currentIndex = array_search($deal->stage, $stageKeys);
                @endphp
                @foreach(\App\Models\Deal::STAGES as $stageKey => $meta)
                    @php
                        $index = array_search($stageKey, $stageKeys);
                        $stepClass = '';
                        if ($deal->stage === \App\Models\Deal::STAGE_CLOSED_LOST && $stageKey === \App\Models\Deal::STAGE_CLOSED_LOST) {
                            $stepClass = 'lost';
                        } elseif ($deal->stage === \App\Models\Deal::STAGE_CLOSED_WON && $stageKey === \App\Models\Deal::STAGE_CLOSED_WON) {
                            $stepClass = 'completed';
                        } elseif ($deal->stage === $stageKey) {
                            $stepClass = 'active';
                        } elseif ($index < $currentIndex && $deal->stage !== \App\Models\Deal::STAGE_CLOSED_LOST) {
                            $stepClass = 'completed';
                        }
                    @endphp
                    <form method="POST" action="{{ route('deals.update-stage', $deal) }}" class="flex-grow-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="stage" value="{{ $stageKey }}">
                        <button type="submit" class="btn w-100 deal-stage-step {{ $stepClass }}" title="Click to move to {{ $meta['label'] }}">
                            {{ $meta['label'] }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        @if($deal->loss_reason)
            <div class="alert alert-danger-subtle text-danger small mt-3 mb-0 p-2">
                <i class="bi bi-x-octagon-fill me-1"></i> <strong>Loss Reason:</strong> {{ $deal->loss_reason }}
            </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Details & Associated Entities -->
    <div class="col-lg-4">
        <!-- Account Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i> Account Details</h6>
            </div>
            <div class="card-body p-3">
                @if($deal->company)
                    <div class="mb-2">
                        <div class="small text-muted">Company</div>
                        <a href="{{ route('companies.show', $deal->company) }}" class="fw-bold text-dark text-decoration-none">
                            {{ $deal->company->name }}
                        </a>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Industry</div>
                        <div>{{ $deal->company->industry ?? 'Not specified' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Contact Phone / Email</div>
                        <div>{{ $deal->company->phone ?? $deal->company->email ?? 'None' }}</div>
                    </div>
                @else
                    <p class="text-muted small mb-0">No company linked to this deal.</p>
                @endif
            </div>
        </div>

        <!-- Primary Contact -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person me-2 text-info"></i> Primary Contact</h6>
            </div>
            <div class="card-body p-3">
                @if($deal->contact)
                    <div class="mb-2">
                        <div class="small text-muted">Full Name</div>
                        <a href="{{ route('contacts.show', $deal->contact) }}" class="fw-bold text-dark text-decoration-none">
                            {{ $deal->contact->full_name }}
                        </a>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Job Title &amp; Department</div>
                        <div>{{ $deal->contact->job_title ?? 'Contact' }} &bull; {{ $deal->contact->department ?? 'General' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Email</div>
                        <div>{{ $deal->contact->email ?? 'None' }}</div>
                    </div>
                    <div class="mb-0">
                        <div class="small text-muted">Phone</div>
                        <div>{{ $deal->contact->phone ?? $deal->contact->mobile ?? 'None' }}</div>
                    </div>
                @else
                    <p class="text-muted small mb-0">No contact linked to this deal.</p>
                @endif
            </div>
        </div>

        <!-- Scope & Notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text me-2 text-secondary"></i> Internal Notes</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-0" style="white-space: pre-line;">{{ $deal->notes ?: 'No notes recorded.' }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline & Tasks -->
    <div class="col-lg-8">
        <!-- Quick Action Bar -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#logDealActivity">
                    <i class="bi bi-telephone me-1"></i> Log Activity
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#createDealTask">
                    <i class="bi bi-check2-circle me-1"></i> Add Task
                </button>
            </div>
            <!-- Log Activity Form Collapse -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="logDealActivity">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                    <input type="hidden" name="company_id" value="{{ $deal->company_id }}">
                    <input type="hidden" name="contact_id" value="{{ $deal->contact_id }}">
                    <input type="hidden" name="status" value="completed">

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="call">Phone Call</option>
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
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Discussion notes and next steps..."></textarea>
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

            <!-- Create Task Collapse -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="createDealTask">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                    <input type="hidden" name="company_id" value="{{ $deal->company_id }}">
                    <input type="hidden" name="contact_id" value="{{ $deal->contact_id }}">
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
                            <option value="medium">Medium Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-danger">Create Task</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deal Tasks -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square me-2 text-danger"></i> Follow-up Tasks</h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($deal->tasks as $tsk)
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
                    <div class="p-3 text-center text-muted small">No tasks assigned to this deal.</div>
                @endforelse
            </div>
        </div>

        <!-- Deal Activities Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Engagement Timeline</h6>
            </div>
            <div class="card-body p-4">
                <div class="activity-timeline">
                    @forelse($deal->activities as $act)
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
