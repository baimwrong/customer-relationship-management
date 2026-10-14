@extends('layouts.app')

@section('title', $contact->full_name)
@section('page_title', 'Contact Profile')

@section('content')
<!-- Header Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info text-white fw-bold d-flex align-items-center justify-content-center fs-3" style="width: 60px; height: 60px;">
                    {{ substr($contact->first_name, 0, 1) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-{{ $contact->status === 'vip' ? 'warning' : 'success' }}-subtle text-{{ $contact->status === 'vip' ? 'warning' : 'success' }} border text-uppercase" style="font-size: 0.7rem;">
                            {{ $contact->status }}
                        </span>
                        @if($contact->company)
                            <a href="{{ route('companies.show', $contact->company) }}" class="badge bg-light text-dark border text-decoration-none">
                                <i class="bi bi-building me-1"></i> {{ $contact->company->name }}
                            </a>
                        @endif
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $contact->full_name }}</h3>
                    <div class="text-muted small">
                        <span>{{ $contact->job_title ?? 'Contact Person' }}</span>
                        @if($contact->department) &bull; <span>{{ $contact->department }}</span> @endif
                        &bull; <i class="bi bi-person-badge me-1"></i> Rep: <strong>{{ $contact->assignedUser?->name ?? 'Unassigned' }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center">
                @if($contact->email)
                    <a href="mailto:{{ $contact->email }}" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-envelope me-1"></i> Email
                    </a>
                @endif
                @if($contact->phone)
                    <a href="tel:{{ $contact->phone }}" class="btn btn-sm btn-outline-success fw-semibold">
                        <i class="bi bi-telephone me-1"></i> Call
                    </a>
                @endif
                <a href="{{ route('deals.create', ['contact_id' => $contact->id, 'company_id' => $contact->company_id]) }}" class="btn btn-sm btn-primary fw-semibold">
                    <i class="bi bi-briefcase me-1"></i> Create Deal
                </a>
                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Details & Associated Company -->
    <div class="col-lg-4">
        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Direct Communication</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="small text-muted d-block">Work Email</span>
                    @if($contact->email)
                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none fw-semibold text-dark">{{ $contact->email }}</a>
                    @else
                        <span class="text-muted small">None</span>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Office Phone</span>
                    <div>{{ $contact->phone ?: 'None' }}</div>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Mobile Phone</span>
                    <div>{{ $contact->mobile ?: 'None' }}</div>
                </div>
                <div class="mb-0">
                    <span class="small text-muted d-block">Organization</span>
                    @if($contact->company)
                        <a href="{{ route('companies.show', $contact->company) }}" class="fw-bold text-dark text-decoration-none">
                            {{ $contact->company->name }}
                        </a>
                    @else
                        <span class="text-muted small">Independent Contact</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text me-2 text-secondary"></i> Contact Notes</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-0" style="white-space: pre-line;">{{ $contact->notes ?: 'No notes recorded.' }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Associated Deals, Activities & Tasks -->
    <div class="col-lg-8">
        <!-- Associated Deals -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase me-2 text-info"></i> Related Opportunities ({{ $contact->deals->count() }})</h6>
                <a href="{{ route('deals.create', ['contact_id' => $contact->id, 'company_id' => $contact->company_id]) }}" class="btn btn-sm btn-link text-decoration-none p-0">+ New Deal</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($contact->deals as $dl)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <a href="{{ route('deals.show', $dl) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $dl->title }}
                            </a>
                            <div class="small text-muted">
                                Stage: <span class="badge bg-{{ $dl->stage_badge_color }}-subtle text-{{ $dl->stage_badge_color }}">{{ $dl->stage_label }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block">${{ number_format($dl->amount, 2) }}</span>
                            <small class="text-muted">{{ $dl->expected_close_date ? $dl->expected_close_date->format('M d, Y') : '' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No deals linked to this contact.</div>
                @endforelse
            </div>
        </div>

        <!-- Quick Log Bar -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#logContactActivity">
                    <i class="bi bi-telephone me-1"></i> Log Activity
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#createContactTask">
                    <i class="bi bi-check2-circle me-1"></i> Schedule Task
                </button>
            </div>

            <!-- Activity Form -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="logContactActivity">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf
                    <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                    <input type="hidden" name="company_id" value="{{ $contact->company_id }}">
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
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Interaction notes..."></textarea>
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

            <!-- Task Form -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="createContactTask">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                    <input type="hidden" name="company_id" value="{{ $contact->company_id }}">
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

        <!-- Activities Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Interaction History</h6>
            </div>
            <div class="card-body p-4">
                <div class="activity-timeline">
                    @forelse($contact->activities as $act)
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
